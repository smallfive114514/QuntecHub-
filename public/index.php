<?php
define('PUBLIC_PATH', __DIR__);
require __DIR__.'/../app/bootstrap.php';

if (!config('installed')) {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $base = app_base_url();
    if ($base !== '' && str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base));
    }
    $uri = '/'.ltrim($uri, '/');
    if ($uri !== '/install') {
        redirect('install');
    }
}

$routes = require BASE_PATH.'/app/routes.php';
$router = new Router();
$routes($router);
$router->dispatch();
