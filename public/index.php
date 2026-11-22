<?php

declare(strict_types=1);

use App\Core\Router;
use App\Core\View;

date_default_timezone_set('Asia/Bangkok');

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
    $className = basename(str_replace('\\', '/', $class));
    $folders = array('Controllers', 'Models', 'Views');
    foreach ($folders as $folder) {
        $file = dirname(__DIR__) . '/app/' . $folder . '/' . $className . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

$view = new View(dirname(__DIR__) . '/app/Views', dirname(__DIR__) . '/app/Views/layouts');
$router = new Router();

set_exception_handler(function (\Throwable $exception) use ($view): void {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    error_log($exception->getMessage());

    if ($requestPath === '/payment/payos/webhook') {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false], JSON_UNESCAPED_UNICODE);
        return;
    }

    http_response_code(500);
    $view->render('errors/error', [
        'title' => 'Hệ thống đang gặp sự cố',
        'errorCode' => '500',
        'errorTitle' => 'Có lỗi xảy ra',
        'errorMessage' => 'Hệ thống chưa thể hoàn tất yêu cầu của bạn lúc này.',
        'errorLabel' => 'Bạn có thể thử lại sau ít phút hoặc quay về trang chủ để tiếp tục.',
        'primaryUrl' => '/',
        'primaryLabel' => 'Về trang chủ',
    ]);
});

$registerRoutes = require dirname(__DIR__) . '/config/routes.php';
$registerRoutes($router, $view);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', rtrim($path, '/') ?: '/');
