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

return static function (Router $router, View $view): void {
    $home = new HomeController($view); $books = new BookController($view); $pages = new PageController($view); $auth = new AuthPageController($view); $cart = new CartPageController($view); $account = new AccountPageController($view); $wishlist = new WishlistController($view);
    $adminDashboard = new AdminDashboardController($view); $adminCatalog = new AdminCatalogController($view); $adminOrders = new AdminOrderController($view); $adminCustomers = new AdminCustomerController($view); $adminVouchers = new AdminVoucherController($view); $adminSettings = new AdminSettingsController($view);
    $router->get('/', [$home, 'index']); $router->get('/books', [$books, 'index']); $router->get('/books/{slug}', [$books, 'detail']); $router->post('/books/{id}/reviews', [$books, 'reviewSubmit']); $router->get('/categories/{slug}', [$books, 'category']); $router->get('/search', [$books, 'search']);
    $router->get('/cart', [$cart, 'cart']); $router->get('/checkout', [$cart, 'checkout']); $router->post('/checkout', [$cart, 'placeOrder']); $router->post('/cart/add/{id}', [$cart, 'add']); $router->post('/cart/update', [$cart, 'update']); $router->post('/cart/remove/{id}', [$cart, 'remove']);
    $router->get('/wishlist', [$wishlist, 'index']); $router->post('/wishlist/add/{id}', [$wishlist, 'add']); $router->post('/wishlist/{id}/remove', [$wishlist, 'remove']);
    $router->get('/payment/payos/return', [$cart, 'payosReturn']); $router->get('/payment/payos/cancel', [$cart, 'payosCancel']); $router->post('/payment/payos/webhook', [$cart, 'payosWebhook']);
    $router->get('/login', [$auth, 'login']); $router->post('/login', [$auth, 'loginSubmit']); $router->get('/forgot-password', [$auth, 'forgotPassword']); $router->post('/forgot-password', [$auth, 'forgotPasswordSubmit']); $router->get('/reset-password', [$auth, 'resetPassword']); $router->post('/reset-password', [$auth, 'resetPasswordSubmit']); $router->post('/verify-email/resend', [$auth, 'resendVerification']); $router->get('/verify-email', [$auth, 'verifyEmail']); $router->get('/auth/google', [$auth, 'googleRedirect']); $router->get('/auth/google/callback', [$auth, 'googleCallback']); $router->get('/register', [$auth, 'register']); $router->post('/register', [$auth, 'registerSubmit']); $router->post('/logout', [$auth, 'logout']);
    $router->get('/account', [$account, 'account']); $router->post('/account', [$account, 'updateProfile']); $router->post('/account/addresses', [$account, 'addressStore']); $router->post('/account/addresses/{id}/update', [$account, 'addressUpdate']); $router->post('/account/addresses/{id}/delete', [$account, 'addressDelete']); $router->post('/account/addresses/{id}/default', [$account, 'addressDefault']); $router->get('/orders', [$cart, 'orderHistory']); $router->get('/about', [$pages, 'about']); $router->get('/contact', [$pages, 'contact']); $router->get('/faq', [$pages, 'faq']); $router->get('/terms', [$pages, 'terms']); $router->get('/privacy', [$pages, 'privacy']); $router->get('/shipping', [$pages, 'shipping']); $router->get('/returns', [$pages, 'returns']); $router->get('/guide', [$pages, 'guide']);
    $router->get('/admin', [$adminDashboard, 'dashboard']); $router->get('/admin/reports', [$adminDashboard, 'reports']);
    $router->get('/admin/books', [$adminCatalog, 'books']); $router->get('/admin/books/create', [$adminCatalog, 'bookCreate']); $router->get('/admin/books/{id}/edit', [$adminCatalog, 'bookEdit']); $router->post('/admin/books', [$adminCatalog, 'bookStore']); $router->post('/admin/books/{id}/update', [$adminCatalog, 'bookUpdate']); $router->post('/admin/books/{id}/delete', [$adminCatalog, 'bookDelete']);
    $router->get('/admin/categories', [$adminCatalog, 'categories']); $router->post('/admin/categories', [$adminCatalog, 'categoryStore']); $router->post('/admin/categories/{id}/update', [$adminCatalog, 'categoryUpdate']); $router->post('/admin/categories/{id}/delete', [$adminCatalog, 'categoryDelete']);
    $router->get('/admin/orders', [$adminOrders, 'orders']); $router->get('/admin/payments', [$adminOrders, 'payments']); $router->post('/admin/orders/{id}/status', [$adminOrders, 'orderStatus']);
    $router->get('/admin/customers', [$adminCustomers, 'customers']); $router->get('/admin/customers/{id}', [$adminCustomers, 'detail']); $router->post('/admin/customers/{id}/profile', [$adminCustomers, 'updateProfile']); $router->post('/admin/customers/{id}/purchase-status', [$adminCustomers, 'updatePurchaseStatus']); $router->post('/admin/customers/{id}/wallet', [$adminCustomers, 'adjustWallet']); $router->get('/admin/vouchers', [$adminVouchers, 'vouchers']); $router->post('/admin/vouchers', [$adminVouchers, 'store']); $router->post('/admin/vouchers/{id}/delete', [$adminVouchers, 'delete']);
    $router->get('/admin/settings', [$adminSettings, 'settings']); $router->post('/admin/settings', [$adminSettings, 'save']);
};
