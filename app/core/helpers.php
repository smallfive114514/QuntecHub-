<?php
function config(string $key = '', $default = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $file = BASE_PATH.'/config/config.php';
        $cfg = file_exists($file) ? require $file : require BASE_PATH.'/config/config.sample.php';
    }
    if ($key === '') {
        return $cfg;
    }
    $val = $cfg;
    foreach (explode('.', $key) as $seg) {
        if (!is_array($val) || !array_key_exists($seg, $val)) {
            return $default;
        }
        $val = $val[$seg];
    }
    return $val;
}

function base_url(string $path = ''): string
{
    return app_base_url().'/'.ltrim($path, '/');
}

function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: '.base_url($path));
    exit;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    return ['id' => $_SESSION['admin_id'], 'username' => $_SESSION['admin_user'] ?? 'admin'];
}

function flash(string $key, $value = null)
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $v = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $v;
}

function old(string $key, $default = '')
{
    return $_SESSION['_old'][$key] ?? $default;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="'.e(csrf_token()).'">';
}

function csrf_check(): bool
{
    $t = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($t) && hash_equals($_SESSION['_csrf'] ?? '', $t);
}

function format_bytes(int|float $size, int $precision = 1): string
{
    $size = (float) $size;
    if ($size <= 0) {
        return '0 B';
    }
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = (int) floor(log($size, 1024));
    $i = min($i, count($units) - 1);
    return round($size / pow(1024, $i), $precision).' '.$units[$i];
}

function time_ago(int $ts): string
{
    $diff = max(0, time() - $ts);
    if ($diff < 60)    return '刚刚';
    if ($diff < 3600)  return floor($diff / 60).' 分钟前';
    if ($diff < 86400) return floor($diff / 3600).' 小时前';
    if ($diff < 2592000) return floor($diff / 86400).' 天前';
    return date('Y-m-d', $ts);
}

function ext_of(string $name): string
{
    $p = pathinfo($name);
    return strtolower($p['extension'] ?? '');
}

function preview_type(string $ext): string
{
    $ext = strtolower($ext);
    if (in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','svg'], true)) return 'image';
    if ($ext === 'pdf') return 'pdf';
    if (in_array($ext, ['txt','log','md','csv','ini','json','xml','yml','yaml','conf'], true)) return 'text';
    if (in_array($ext, ['mp4','webm','ogg'], true)) return 'video';
    if (in_array($ext, ['mp3','wav','m4a'], true)) return 'audio';
    if ($ext === 'docx') return 'docx';
    if (in_array($ext, ['xlsx','xls'], true)) return 'xlsx';
    return 'download';
}

function safe_name(string $name): string
{
    $name = trim($name);
    $name = str_replace(['/', '\\', '..', "\0"], '_', $name);
    $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name);
    return $name !== '' ? $name : '未命名';
}

function file_icon(string $ext): string
{
    $ext = strtolower($ext);
    $map = [
        'jpg'=>'bi-image','jpeg'=>'bi-image','png'=>'bi-image','gif'=>'bi-image','webp'=>'bi-image','bmp'=>'bi-image','svg'=>'bi-image',
        'pdf'=>'bi-file-earmark-pdf',
        'doc'=>'bi-file-earmark-word','docx'=>'bi-file-earmark-word',
        'xls'=>'bi-file-earmark-excel','xlsx'=>'bi-file-earmark-excel',
        'ppt'=>'bi-file-earmark-ppt','pptx'=>'bi-file-earmark-ppt',
        'zip'=>'bi-file-earmark-zip','rar'=>'bi-file-earmark-zip','7z'=>'bi-file-earmark-zip',
        'mp4'=>'bi-file-earmark-play','webm'=>'bi-file-earmark-play','mov'=>'bi-file-earmark-play',
        'mp3'=>'bi-file-earmark-music','wav'=>'bi-file-earmark-music','m4a'=>'bi-file-earmark-music',
        'txt'=>'bi-file-earmark-text','md'=>'bi-file-earmark-text','csv'=>'bi-file-earmark-text','json'=>'bi-file-earmark-text',
    ];
    return $map[$ext] ?? 'bi-file-earmark';
}

function upload_dir(): string
{
    $dir = config('app.upload_dir');
    return $dir ?: BASE_PATH.'/public/uploads';
}

function make_storage_path(int $projectId, string $origName): string
{
    $y = date('Y'); $m = date('m');
    $uniq = bin2hex(random_bytes(8));
    $safe = safe_name($origName);
    return 'projects/'.$projectId.'/'.$y.'/'.$m.'/'.$uniq.'_'.$safe;
}

function ensure_dir(string $rel): void
{
    $abs = rtrim(upload_dir(), '/').'/'.$rel;
    if (!is_dir(dirname($abs))) {
        @mkdir(dirname($abs), 0755, true);
    }
}
