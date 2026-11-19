<?php

declare(strict_types=1);

use App\Controllers\AccountPageController;
use App\Controllers\AdminCatalogController;
use App\Controllers\AdminCustomerController;
use App\Controllers\AdminDashboardController;
use App\Controllers\AdminOrderController;
use App\Controllers\AdminSettingsController;
use App\Controllers\AdminVoucherController;
use App\Controllers\AuthPageController;
use App\Controllers\BookController;
use App\Controllers\CartPageController;
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Core\Router;
use App\Core\View;

return static function (Router $router, View $view): void {
<<<<<<< Updated upstream
    $home = new HomeController($view); $books = new BookController($view); $pages = new PageController($view); $auth = new AuthPageController($view); $cart = new CartPageController($view); $account = new AccountPageController($view);
    $adminDashboard = new AdminDashboardController($view); $adminCatalog = new AdminCatalogController($view); $adminOrders = new AdminOrderController($view); $adminCustomers = new AdminCustomerController($view); $adminVouchers = new AdminVoucherController($view); $adminSettings = new AdminSettingsController($view);
    $router->get('/', [$home, 'index']); $router->get('/books', [$books, 'index']); $router->get('/books/{slug}', [$books, 'detail']); $router->post('/books/{id}/reviews', [$books, 'reviewSubmit']); $router->get('/categories/{slug}', [$books, 'category']); $router->get('/search', [$books, 'search']);
    $router->get('/cart', [$cart, 'cart']); $router->get('/checkout', [$cart, 'checkout']); $router->post('/checkout', [$cart, 'placeOrder']); $router->post('/cart/add/{id}', [$cart, 'add']); $router->post('/cart/update', [$cart, 'update']); $router->post('/cart/remove/{id}', [$cart, 'remove']);
    $router->get('/payment/payos/return', [$cart, 'payosReturn']); $router->get('/payment/payos/cancel', [$cart, 'payosCancel']); $router->post('/payment/payos/webhook', [$cart, 'payosWebhook']);
    $router->get('/login', [$auth, 'login']); $router->post('/login', [$auth, 'loginSubmit']); $router->get('/register', [$auth, 'register']); $router->post('/register', [$auth, 'registerSubmit']); $router->post('/logout', [$auth, 'logout']);
    $router->get('/account', [$account, 'account']); $router->post('/account', [$account, 'updateName']); $router->get('/orders', [$cart, 'orderHistory']); $router->get('/about', [$pages, 'about']); $router->get('/contact', [$pages, 'contact']);
    $router->get('/admin', [$adminDashboard, 'dashboard']); $router->get('/admin/reports', [$adminDashboard, 'reports']);
    $router->get('/admin/books', [$adminCatalog, 'books']); $router->get('/admin/books/create', [$adminCatalog, 'bookCreate']); $router->get('/admin/books/{id}/edit', [$adminCatalog, 'bookEdit']); $router->post('/admin/books', [$adminCatalog, 'bookStore']); $router->post('/admin/books/{id}/update', [$adminCatalog, 'bookUpdate']); $router->post('/admin/books/{id}/delete', [$adminCatalog, 'bookDelete']);
    $router->get('/admin/categories', [$adminCatalog, 'categories']); $router->post('/admin/categories', [$adminCatalog, 'categoryStore']); $router->post('/admin/categories/{id}/update', [$adminCatalog, 'categoryUpdate']); $router->post('/admin/categories/{id}/delete', [$adminCatalog, 'categoryDelete']);
    $router->get('/admin/orders', [$adminOrders, 'orders']); $router->get('/admin/payments', [$adminOrders, 'payments']); $router->post('/admin/orders/{id}/status', [$adminOrders, 'orderStatus']);
    $router->get('/admin/customers', [$adminCustomers, 'customers']); $router->get('/admin/vouchers', [$adminVouchers, 'vouchers']); $router->post('/admin/vouchers', [$adminVouchers, 'store']); $router->post('/admin/vouchers/{id}/delete', [$adminVouchers, 'delete']);
    $router->get('/admin/settings', [$adminSettings, 'settings']); $router->post('/admin/settings', [$adminSettings, 'save']);
};
=======

    // Khởi tạo Controller
    $home = new HomeController($view);

    $books = new BookController($view);

    $pages = new PageController($view);

    $auth = new AuthPageController($view);

    $cart = new CartPageController($view);

    $account = new AccountPageController($view);

    $adminDashboard = new AdminDashboardController($view);

    $adminCatalog = new AdminCatalogController($view);

    $adminOrders = new AdminOrderController($view);

    $adminCustomers = new AdminCustomerController($view);

    $adminVouchers = new AdminVoucherController($view);

    $adminSettings = new AdminSettingsController($view);


    // Trang chủ
    $router->get('/', [$home, 'index']);


    // Sách
    $router->get('/books', [$books, 'index']);

    $router->get('/books/{slug}', [$books, 'detail']);

    $router->post('/books/{id}/reviews', [$books, 'reviewSubmit']);

    $router->get('/categories/{slug}', [$books, 'category']);

    $router->get('/search', [$books, 'search']);


    // Giỏ hàng
    $router->get('/cart', [$cart, 'cart']);

    $router->get('/checkout', [$cart, 'checkout']);

    $router->post('/checkout', [$cart, 'placeOrder']);

    $router->post('/cart/add/{id}', [$cart, 'add']);

    $router->post('/cart/update', [$cart, 'update']);

    $router->post('/cart/remove/{id}', [$cart, 'remove']);


    // Thanh toán PayOS
    $router->get(
        '/payment/payos/return',
        [$cart, 'payosReturn']
    );

    $router->get(
        '/payment/payos/cancel',
        [$cart, 'payosCancel']
    );

    $router->post(
        '/payment/payos/webhook',
        [$cart, 'payosWebhook']
    );


    // Đăng nhập và đăng ký
    $router->get('/login', [$auth, 'login']);

    $router->post('/login', [$auth, 'loginSubmit']);

    $router->get('/auth/google', [$auth, 'googleRedirect']);

    $router->get(
        '/auth/google/callback',
        [$auth, 'googleCallback']
    );

    $router->get('/register', [$auth, 'register']);

    $router->post('/register', [$auth, 'registerSubmit']);

    $router->post('/logout', [$auth, 'logout']);


    // Tài khoản
    $router->get('/account', [$account, 'account']);

    $router->post('/account', [$account, 'updateName']);

    // Đánh dấu một thông báo đã đọc
    $router->post(
        '/account/notifications/{id}/read',
        [$account, 'markNotificationRead']
    );

    // Đánh dấu tất cả thông báo đã đọc
    $router->post(
        '/account/notifications/read-all',
        [$account, 'markAllNotificationsRead']
    );


    // Lịch sử đơn hàng
    $router->get('/orders', [$cart, 'orderHistory']);


    // Trang giới thiệu và liên hệ
    $router->get('/about', [$pages, 'about']);

    $router->get('/contact', [$pages, 'contact']);


    // Admin
    $router->get('/admin', [$adminDashboard, 'dashboard']);

    $router->get('/admin/reports', [$adminDashboard, 'reports']);


    // Quản lý sách
    $router->get('/admin/books', [$adminCatalog, 'books']);

    $router->get(
        '/admin/books/create',
        [$adminCatalog, 'bookCreate']
    );

    $router->get(
        '/admin/books/{id}/edit',
        [$adminCatalog, 'bookEdit']
    );

    $router->post(
        '/admin/books',
        [$adminCatalog, 'bookStore']
    );

    $router->post(
        '/admin/books/{id}/update',
        [$adminCatalog, 'bookUpdate']
    );

    $router->post(
        '/admin/books/{id}/delete',
        [$adminCatalog, 'bookDelete']
    );


    // Quản lý danh mục
    $router->get(
        '/admin/categories',
        [$adminCatalog, 'categories']
    );

    $router->post(
        '/admin/categories',
        [$adminCatalog, 'categoryStore']
    );

    $router->post(
        '/admin/categories/{id}/update',
        [$adminCatalog, 'categoryUpdate']
    );

    $router->post(
        '/admin/categories/{id}/delete',
        [$adminCatalog, 'categoryDelete']
    );


    // Quản lý đơn hàng
    $router->get('/admin/orders', [$adminOrders, 'orders']);

    $router->get('/admin/payments', [$adminOrders, 'payments']);

    $router->post(
        '/admin/orders/{id}/status',
        [$adminOrders, 'orderStatus']
    );


    // Khách hàng
    $router->get(
        '/admin/customers',
        [$adminCustomers, 'customers']
    );


    // Voucher
    $router->get(
        '/admin/vouchers',
        [$adminVouchers, 'vouchers']
    );

    $router->post(
        '/admin/vouchers',
        [$adminVouchers, 'store']
    );

    $router->post(
        '/admin/vouchers/{id}/delete',
        [$adminVouchers, 'delete']
    );


    // Cài đặt
    $router->get(
        '/admin/settings',
        [$adminSettings, 'settings']
    );

    $router->post(
        '/admin/settings',
        [$adminSettings, 'save']
    );
};
>>>>>>> Stashed changes
