<?php

declare(strict_types=1);

use App\Controllers\AccountPageController;
use App\Controllers\AdminPageController;
use App\Controllers\AuthPageController;
use App\Controllers\BookController;
use App\Controllers\CartPageController;
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Core\Router;
use App\Core\View;

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
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

$home = new HomeController($view);
$books = new BookController($view);
$pages = new PageController($view);
$auth = new AuthPageController($view);
$cart = new CartPageController($view);
$account = new AccountPageController($view);
$admin = new AdminPageController($view);

$router->get('/', [$home, 'index']);
$router->get('/books', [$books, 'index']);
$router->get('/books/{slug}', [$books, 'detail']);
$router->post('/books/{id}/reviews', [$books, 'reviewSubmit']);
$router->get('/categories/{slug}', [$books, 'category']);
$router->get('/search', [$books, 'search']);
$router->get('/cart', [$cart, 'cart']);
$router->get('/checkout', [$cart, 'checkout']);
$router->post('/checkout', [$cart, 'placeOrder']);
$router->get('/payment/payos/return', [$cart, 'payosReturn']);
$router->get('/payment/payos/cancel', [$cart, 'payosCancel']);
$router->post('/payment/payos/webhook', [$cart, 'payosWebhook']);
$router->post('/cart/add/{id}', [$cart, 'add']);
$router->post('/cart/update', [$cart, 'update']);
$router->post('/cart/remove/{id}', [$cart, 'remove']);
$router->get('/login', [$auth, 'login']);
$router->get('/register', [$auth, 'register']);
$router->post('/logout', [$auth, 'logout']);
$router->get('/admin/books/{id}/edit', [$admin, 'bookEdit']);
$router->get('/admin/books/create', [$admin, 'bookCreate']);
$router->get('/admin/books', [$admin, 'books']);
$router->post('/login', [$auth, 'loginSubmit']);
$router->post('/register', [$auth, 'registerSubmit']);
$router->post('/admin/books', [$admin, 'bookStore']);
$router->post('/admin/books/{id}/update', [$admin, 'bookUpdate']);
$router->post('/admin/books/{id}/delete', [$admin, 'bookDelete']);
$router->post('/admin/categories', [$admin, 'categoryStore']);
$router->post('/admin/categories/{id}/update', [$admin, 'categoryUpdate']);
$router->post('/admin/categories/{id}/delete', [$admin, 'categoryDelete']);
$router->post('/admin/orders/{id}/status', [$admin, 'orderStatus']);
$router->post('/admin/vouchers', [$admin, 'voucherStore']);
$router->post('/admin/vouchers/{id}/delete', [$admin, 'voucherDelete']);
$router->get('/account', [$account, 'account']);
$router->get('/orders', [$cart, 'orderHistory']);
$router->get('/about', fn() => $pages->show('about'));
$router->get('/contact', fn() => $pages->show('contact'));
$router->get('/admin', [$admin, 'dashboard']);
$router->get('/admin/reports', [$admin, 'reports']);
$router->get('/admin/categories', [$admin, 'categories']);
$router->get('/admin/orders', [$admin, 'orders']);
$router->get('/admin/payments', [$admin, 'payments']);
$router->get('/admin/settings', [$admin, 'settings']);
$router->post('/admin/settings', [$admin, 'settingsSave']);
$router->get('/admin/customers', [$admin, 'customers']);
$router->get('/admin/vouchers', [$admin, 'vouchers']);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', rtrim($path, '/') ?: '/');
