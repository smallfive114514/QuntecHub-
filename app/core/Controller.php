<?php
abstract class Controller
{
    protected function view(string $template, array $data = []): void
    {
        $data['currentUser'] = current_user();
        $content = $this->capture($template, $data);
        $layoutData = array_merge($data, ['content' => $content]);
        extract($layoutData, EXTR_SKIP);
        require BASE_PATH.'/app/Views/layout.php';
    }

    protected function plain(string $template, array $data = []): void
    {
        $data['currentUser'] = current_user();
        echo $this->capture($template, $data);
    }

    private function capture(string $template, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require BASE_PATH.'/app/Views/'.$template.'.php';
        return (string) ob_get_clean();
    }

    protected function redirect(string $path): void
    {
        redirect($path);
    }

    protected function requireAuth(): void
    {
        if (!is_logged_in()) {
            $back = $_SERVER['REQUEST_URI'] ?? '';
            redirect('login?next='.urlencode($back));
        }
    }

    protected function requireCsrf(): void
    {
        if (!csrf_check()) {
            http_response_code(419);
            exit('请求已过期，请返回重试。');
        }
    }
}
