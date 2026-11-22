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
use App\Controllers\WishlistController;
use App\Core\Router;
use App\Core\View;

/* Chỉ khai báo đường dẫn và Controller. Không viết SQL trong file này. */
return function (Router $router, View $view): void {
    $homeController = new HomeController($view);
    $bookController = new BookController($view);
    $pageController = new PageController($view);
    $authController = new AuthPageController($view);
    $cartController = new CartPageController($view);
    $accountController = new AccountPageController($view);
    $wishlistController = new WishlistController($view);
    $adminDashboardController = new AdminDashboardController($view);
    $adminCatalogController = new AdminCatalogController($view);
    $adminOrderController = new AdminOrderController($view);
    $adminCustomerController = new AdminCustomerController($view);
    $adminVoucherController = new AdminVoucherController($view);
    $adminSettingsController = new AdminSettingsController($view);

    // Cửa hàng
    $router->get('/', [$homeController, 'index']);
    $router->get('/books', [$bookController, 'index']);
    $router->get('/books/{slug}', [$bookController, 'detail']);
    $router->post('/books/{id}/reviews', [$bookController, 'reviewSubmit']);
    $router->get('/categories/{slug}', [$bookController, 'category']);
    $router->get('/search', [$bookController, 'search']);

    // Giỏ hàng, thanh toán và đơn hàng
    $router->get('/cart', [$cartController, 'cart']);
    $router->post('/cart/add/{id}', [$cartController, 'add']);
    $router->post('/cart/update', [$cartController, 'update']);
    $router->post('/cart/remove/{id}', [$cartController, 'remove']);
    $router->get('/checkout', [$cartController, 'checkout']);
    $router->post('/checkout', [$cartController, 'placeOrder']);
    $router->get('/orders', [$cartController, 'orderHistory']);

    // Wishlist
    $router->get('/wishlist', [$wishlistController, 'index']);
    $router->post('/wishlist/add/{id}', [$wishlistController, 'add']);
    $router->post('/wishlist/{id}/remove', [$wishlistController, 'remove']);

    // PayOS
    $router->get('/payment/payos/return', [$cartController, 'payosReturn']);
    $router->get('/payment/payos/cancel', [$cartController, 'payosCancel']);
    $router->post('/payment/payos/webhook', [$cartController, 'payosWebhook']);

    // Đăng nhập và tài khoản
    $router->get('/login', [$authController, 'login']);
    $router->post('/login', [$authController, 'loginSubmit']);
    $router->get('/register', [$authController, 'register']);
    $router->post('/register', [$authController, 'registerSubmit']);
    $router->post('/logout', [$authController, 'logout']);
    $router->get('/forgot-password', [$authController, 'forgotPassword']);
    $router->post('/forgot-password', [$authController, 'forgotPasswordSubmit']);
    $router->get('/reset-password', [$authController, 'resetPassword']);
    $router->post('/reset-password', [$authController, 'resetPasswordSubmit']);
    $router->get('/verify-email', [$authController, 'verifyEmail']);
    $router->post('/verify-email/resend', [$authController, 'resendVerification']);
    $router->get('/auth/google', [$authController, 'googleRedirect']);
    $router->get('/auth/google/callback', [$authController, 'googleCallback']);
    $router->get('/account', [$accountController, 'account']);
    $router->post('/account', [$accountController, 'updateProfile']);
    $router->post('/account/addresses', [$accountController, 'addressStore']);
    $router->post('/account/addresses/{id}/update', [$accountController, 'addressUpdate']);
    $router->post('/account/addresses/{id}/delete', [$accountController, 'addressDelete']);
    $router->post('/account/addresses/{id}/default', [$accountController, 'addressDefault']);

    // Trang thông tin
    $router->get('/about', [$pageController, 'about']);
    $router->get('/contact', [$pageController, 'contact']);
    $router->get('/faq', [$pageController, 'faq']);
    $router->get('/terms', [$pageController, 'terms']);
    $router->get('/privacy', [$pageController, 'privacy']);
    $router->get('/shipping', [$pageController, 'shipping']);
    $router->get('/returns', [$pageController, 'returns']);
    $router->get('/guide', [$pageController, 'guide']);

    // Quản trị
    $router->get('/admin', [$adminDashboardController, 'dashboard']);
    $router->get('/admin/reports', [$adminDashboardController, 'reports']);
    $router->get('/admin/books', [$adminCatalogController, 'books']);
    $router->get('/admin/books/create', [$adminCatalogController, 'bookCreate']);
    $router->get('/admin/books/{id}/edit', [$adminCatalogController, 'bookEdit']);
    $router->post('/admin/books', [$adminCatalogController, 'bookStore']);
    $router->post('/admin/books/{id}/update', [$adminCatalogController, 'bookUpdate']);
    $router->post('/admin/books/{id}/delete', [$adminCatalogController, 'bookDelete']);
    $router->get('/admin/categories', [$adminCatalogController, 'categories']);
    $router->post('/admin/categories', [$adminCatalogController, 'categoryStore']);
    $router->post('/admin/categories/{id}/update', [$adminCatalogController, 'categoryUpdate']);
    $router->post('/admin/categories/{id}/delete', [$adminCatalogController, 'categoryDelete']);
    $router->get('/admin/orders', [$adminOrderController, 'orders']);
    $router->get('/admin/payments', [$adminOrderController, 'payments']);
    $router->post('/admin/orders/{id}/status', [$adminOrderController, 'orderStatus']);
    $router->get('/admin/customers', [$adminCustomerController, 'customers']);
    $router->get('/admin/customers/{id}', [$adminCustomerController, 'detail']);
    $router->post('/admin/customers/{id}/profile', [$adminCustomerController, 'updateProfile']);
    $router->post('/admin/customers/{id}/purchase-status', [$adminCustomerController, 'updatePurchaseStatus']);
    $router->post('/admin/customers/{id}/wallet', [$adminCustomerController, 'adjustWallet']);
    $router->get('/admin/vouchers', [$adminVoucherController, 'vouchers']);
    $router->post('/admin/vouchers', [$adminVoucherController, 'store']);
    $router->post('/admin/vouchers/{id}/delete', [$adminVoucherController, 'delete']);
    $router->get('/admin/settings', [$adminSettingsController, 'settings']);
    $router->post('/admin/settings', [$adminSettingsController, 'save']);
};
