<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Session;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use App\Models\SystemSetting;
use RuntimeException;
use Throwable;

final class AdminPageController extends Controller
{
    private function database(): Database { return new Database(require dirname(__DIR__, 2) . '/config/database.php'); }
    private function authorize(): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
            http_response_code(403);
            $this->view->render('errors/error', ['title'=>'Không có quyền truy cập','errorCode'=>'403','errorTitle'=>'Bạn không có quyền truy cập','errorMessage'=>'Tài khoản hiện tại không được phép mở khu vực quản trị.','errorLabel'=>'Hãy đăng nhập bằng tài khoản quản trị hoặc quay lại cửa hàng.','primaryUrl'=>'/','primaryLabel'=>'Về cửa hàng']);
            exit;
        }
    }
    public function dashboard(): void { $this->authorize(); $database = $this->database(); $metrics = (new Order($database))->dashboardMetrics(); $this->view->render('admin/dashboard', ['title'=>'Tổng quan','bookCount'=>(new Book($database))->countAll(),'categoryCount'=>(new Category($database))->countAll(),'orderCount'=>(new Order($database))->countAll(),'customerCount'=>(new User($database))->countAll(),'metrics'=>$metrics], 'admin'); }
    public function reports(): void { $this->authorize(); $this->view->render('admin/reports', ['title'=>'Báo cáo bán hàng','reports'=>(new Order($this->database()))->businessReports()], 'admin'); }
    public function books(): void { $this->authorize(); $this->view->render('admin/books', ['title'=>'Quản lý sách','books'=>(new Book($this->database()))->allForAdmin()], 'admin'); }
    public function bookCreate(): void { $this->authorize(); $this->view->render('admin/book_form', ['title'=>'Thêm sách','categories'=>(new Category($this->database()))->all()], 'admin'); }
    public function bookEdit(string $id): void { $this->authorize(); $this->view->render('admin/book_form', ['title'=>'Chỉnh sửa sách','book'=>(new Book($this->database()))->findById((int)$id),'categories'=>(new Category($this->database()))->all()], 'admin'); }
    public function categories(): void { $this->authorize(); $this->view->render('admin/categories', ['title'=>'Danh mục','categories'=>(new Category($this->database()))->all()], 'admin'); }
    public function orders(): void { $this->authorize(); $this->view->render('admin/orders', ['title'=>'Đơn hàng','orders'=>(new Order($this->database()))->allForAdmin()], 'admin'); }
    public function payments(): void
    {
        $this->authorize();
        $method = trim((string)($_GET['method'] ?? '')) ?: null;
        $status = trim((string)($_GET['status'] ?? '')) ?: null;
        $this->view->render('admin/payments', [
            'title' => 'Quản lý thanh toán',
            'payments' => (new Order($this->database()))->paymentsForAdmin($method, $status),
            'method' => $method,
            'status' => $status,
        ], 'admin');
    }
    public function customers(): void { $this->authorize(); $this->view->render('admin/customers', ['title'=>'Khách hàng','customers'=>(new User($this->database()))->allForAdmin()], 'admin'); }
    public function vouchers(): void { $this->authorize(); $this->view->render('admin/vouchers', ['title'=>'Mã giảm giá','vouchers'=>(new Voucher($this->database()))->allForAdmin()], 'admin'); }
    public function settings(): void
    {
        $this->authorize();
        $settings = new SystemSetting($this->database());
        $config = $settings->payOSConfig();
        $this->view->render('admin/settings', ['title'=>'Cài đặt hệ thống', 'settings'=>$settings->allForAdmin(), 'payosConfig'=>$config, 'payosConfigured'=>$this->payOSConfigured($config)], 'admin');
    }

    public function settingsSave(): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $user = Session::get('user');
            (new SystemSetting($this->database()))->saveMany([
                'SHOP_PHONE' => ['value'=>trim((string)($_POST['shop_phone'] ?? '')), 'secret'=>false],
                'SHOP_EMAIL' => ['value'=>trim((string)($_POST['shop_email'] ?? '')), 'secret'=>false],
                'SHIPPING_FEE' => ['value'=>(string)max(0, (int)($_POST['shipping_fee'] ?? 0)), 'secret'=>false],
                'PAYOS_CLIENT_ID' => ['value'=>trim((string)($_POST['payos_client_id'] ?? '')), 'secret'=>true],
                'PAYOS_API_KEY' => ['value'=>trim((string)($_POST['payos_api_key'] ?? '')), 'secret'=>true],
                'PAYOS_CHECKSUM_KEY' => ['value'=>trim((string)($_POST['payos_checksum_key'] ?? '')), 'secret'=>true],
            ], (int)$user['id']);
            header('Location: /admin/settings?saved=1');
        } catch (Throwable $exception) {
            http_response_code(422);
            $settings = new SystemSetting($this->database());
            $config = $settings->payOSConfig();
            $this->view->render('admin/settings', ['title'=>'Cài đặt hệ thống','error'=>$exception->getMessage(),'settings'=>$settings->allForAdmin(), 'payosConfig'=>$config, 'payosConfigured'=>$this->payOSConfigured($config)], 'admin');
        }
    }

    private function payOSConfigured(array $config): bool
    {
        return trim((string)($config['client_id'] ?? '')) !== ''
            && trim((string)($config['api_key'] ?? '')) !== ''
            && trim((string)($config['checksum_key'] ?? '')) !== '';
    }

    private function bookData(): array
    {
        $title = trim((string)($_POST['title'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));
        $author = trim((string)($_POST['author'] ?? ''));
        $price = (int)($_POST['price'] ?? 0);
        $original = trim((string)($_POST['original_price'] ?? ''));
        $categoryId = (int)($_POST['category_id'] ?? 0);
        if ($title === '' || $slug === '' || $author === '' || $categoryId < 1 || $price < 0 || ($original !== '' && (int)$original < $price)) {
            throw new RuntimeException('Vui lòng kiểm tra tên, slug, tác giả, danh mục và giá sách.');
        }
        return ['category_id'=>$categoryId,'title'=>$title,'slug'=>$slug,'author'=>$author,'publisher'=>trim((string)($_POST['publisher'] ?? '')) ?: null,'price'=>$price,'original_price'=>$original === '' ? null : (int)$original,'description'=>trim((string)($_POST['description'] ?? '')) ?: null,'stock_quantity'=>max(0,(int)($_POST['stock_quantity'] ?? 0)),'status'=>in_array($_POST['status'] ?? 'draft', ['draft','published','hidden'], true) ? $_POST['status'] : 'draft'];
    }
    public function bookStore(): void { $this->authorize(); try { Csrf::verify($_POST['_csrf'] ?? null); (new Book($this->database()))->create($this->bookData()); header('Location: /admin/books'); } catch (Throwable $e) { http_response_code(422); $this->view->render('admin/book_form', ['title'=>'Thêm sách','error'=>$e->getMessage(),'categories'=>(new Category($this->database()))->all()], 'admin'); } }
    public function bookUpdate(string $id): void { $this->authorize(); try { Csrf::verify($_POST['_csrf'] ?? null); (new Book($this->database()))->update((int)$id, $this->bookData()); header('Location: /admin/books'); } catch (Throwable $e) { http_response_code(422); $this->view->render('admin/book_form', ['title'=>'Chỉnh sửa sách','error'=>$e->getMessage(),'book'=>(new Book($this->database()))->findById((int)$id),'categories'=>(new Category($this->database()))->all()], 'admin'); } }
    public function bookDelete(string $id): void { $this->authorize(); Csrf::verify($_POST['_csrf'] ?? null); (new Book($this->database()))->delete((int)$id); header('Location: /admin/books'); }

    private function categoryData(): array
    {
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));
        if ($name === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new RuntimeException('Tên danh mục không được trống và slug chỉ dùng chữ thường, số, dấu gạch ngang.');
        }
        return [$name, $slug, trim((string)($_POST['description'] ?? '')) ?: null];
    }

    public function categoryStore(): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            [$name, $slug, $description] = $this->categoryData();
            (new Category($this->database()))->create($name, $slug, $description);
            header('Location: /admin/categories');
        } catch (Throwable $exception) {
            http_response_code(422);
            $this->view->render('admin/categories', ['title'=>'Danh mục','error'=>$exception->getMessage(),'categories'=>(new Category($this->database()))->all()], 'admin');
        }
    }

    public function categoryUpdate(string $id): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            [$name, $slug, $description] = $this->categoryData();
            (new Category($this->database()))->update((int)$id, $name, $slug, $description);
            header('Location: /admin/categories');
        } catch (Throwable $exception) {
            http_response_code(422);
            $this->view->render('admin/categories', ['title'=>'Danh mục','error'=>$exception->getMessage(),'categories'=>(new Category($this->database()))->all()], 'admin');
        }
    }

    public function categoryDelete(string $id): void
    {
        $this->authorize();
        Csrf::verify($_POST['_csrf'] ?? null);
        (new Category($this->database()))->delete((int)$id);
        header('Location: /admin/categories');
    }

    public function orderStatus(string $id): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $user = Session::get('user');
            (new Order($this->database()))->updateStatus(
                (int)$id,
                (string)($_POST['status'] ?? ''),
                is_array($user) ? (int)$user['id'] : null
            );
            header('Location: /admin/orders');
        } catch (Throwable $exception) {
            http_response_code(422);
            $this->view->render('admin/orders', ['title'=>'Đơn hàng','error'=>$exception->getMessage(),'orders'=>(new Order($this->database()))->allForAdmin()], 'admin');
        }
    }

    public function voucherStore(): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            (new Voucher($this->database()))->create((string)($_POST['code'] ?? ''), (string)($_POST['type'] ?? ''), (int)($_POST['value'] ?? 0), (int)($_POST['min_order_amount'] ?? 0), trim((string)($_POST['starts_at'] ?? '')) ?: null, trim((string)($_POST['expires_at'] ?? '')) ?: null, ($_POST['usage_limit'] ?? '') === '' ? null : (int)$_POST['usage_limit']);
            header('Location: /admin/vouchers');
        } catch (Throwable $exception) {
            http_response_code(422);
            $this->view->render('admin/vouchers', ['title'=>'Mã giảm giá','error'=>$exception->getMessage(),'vouchers'=>(new Voucher($this->database()))->allForAdmin()], 'admin');
        }
    }

    public function voucherDelete(string $id): void
    {
        $this->authorize();
        Csrf::verify($_POST['_csrf'] ?? null);
        (new Voucher($this->database()))->delete((int)$id);
        header('Location: /admin/vouchers');
    }
}
