<?php
/**
 * 安装向导：检测连接 → 建表 → 建管理员 → 写 config.php
 */
class InstallController extends Controller
{
    public function index(): void
    {
        if (config('installed')) {
            redirect('login');
        }
        $this->plain('install', [
            'error'  => flash('error'),
            'old'    => [
                'host' => old('host', 'localhost'),
                'port' => old('port', '3306'),
                'name' => old('name'),
                'user' => old('user'),
            ],
        ]);
    }

    public function store(): void
    {
        if (config('installed')) {
            redirect('login');
        }
        $host = trim($_POST['host'] ?? 'localhost');
        $port = (int) ($_POST['port'] ?? 3306);
        $name = trim($_POST['name'] ?? '');
        $user = trim($_POST['user'] ?? '');
        $pass = $_POST['pass'] ?? '';
        $adminUser = trim($_POST['admin_user'] ?? 'admin');
        $adminPass = $_POST['admin_pass'] ?? '';

        if ($name === '' || $user === '' || $adminUser === '' || strlen($adminPass) < 6) {
            $_SESSION['_old'] = $_POST;
            flash('error', '数据库名/用户名不能为空，管理员密码至少 6 位。');
            redirect('install');
        }

        try {
            $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port);
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (Throwable $e) {
            $_SESSION['_old'] = $_POST;
            flash('error', '数据库连接失败：'.$e->getMessage().'。请检查地址/账号/密码，以及该库是否存在。');
            redirect('install');
        }

        try {
            $pdo->exec("USE `".$name."`");
        } catch (Throwable $e) {
            try {
                $pdo->exec("CREATE DATABASE `".$name."` DEFAULT CHARSET utf8mb4");
                $pdo->exec("USE `".$name."`");
            } catch (Throwable $e2) {
                $_SESSION['_old'] = $_POST;
                flash('error', '数据库 "'.$name.'" 不存在且自动创建失败，请先在宝塔创建该数据库。');
                redirect('install');
            }
        }

        $sql = file_get_contents(BASE_PATH.'/install.sql');
        if ($sql === false) {
            flash('error', 'install.sql 读取失败，请检查文件是否完整。');
            redirect('install');
        }
        try {
            $this->execMulti($pdo, $sql);
        } catch (Throwable $e) {
            $_SESSION['_old'] = $_POST;
            flash('error', '建表失败：'.$e->getMessage());
            redirect('install');
        }

        $hash = password_hash($adminPass, PASSWORD_BCRYPT);
        $pdo->prepare('DELETE FROM admin')->execute();
        $stmt = $pdo->prepare('INSERT INTO admin (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$adminUser, $hash]);

        $cfg = [
            'db' => [
                'host' => $host, 'port' => $port, 'name' => $name,
                'user' => $user, 'pass' => $pass, 'charset' => 'utf8mb4',
            ],
            'app' => [
                'name' => 'QuntecHub 项目文件管理系统',
                'upload_dir' => BASE_PATH.'/public/uploads',
                'max_size'  => 0,
                'allow_ext' => [],
            ],
            'installed' => true,
        ];
        $code = "<?php\nreturn ".var_export($cfg, true).";\n";
        if (@file_put_contents(BASE_PATH.'/config/config.php', $code) === false) {
            flash('error', 'config/config.php 写入失败，请给 config 目录可写权限（宝塔：755 或更高）。');
            redirect('install');
        }

        flash('success', '安装完成！请用刚设置的管理员账号登录。');
        redirect('login');
    }

    private function execMulti(PDO $pdo, string $sql): void
    {
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        $sql = preg_replace('/--.*$/m', '', $sql);
        $parts = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($parts as $part) {
            if ($part !== '') {
                $pdo->exec($part);
            }
        }
    }
}