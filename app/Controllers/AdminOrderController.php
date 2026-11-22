<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Order;
use Throwable;

final class AdminOrderController extends AdminController
{
    public function orders(): void
    {
        $this->authorize();
        $data = array('title' => 'Đơn hàng');
        $data['orders'] = (new Order($this->database()))->allForAdmin();
        $this->view->render('admin/orders', $data, 'admin');
    }

    public function payments(): void
    {
        $this->authorize();
        $method = trim((string)($_GET['method'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        $orderModel = new Order($this->database());
        $data = array('title' => 'Quản lý thanh toán');
        $data['payments'] = $orderModel->paymentsForAdmin($method ?: null, $status ?: null);
        $data['method'] = $method ?: null;
        $data['status'] = $status ?: null;
        $this->view->render('admin/payments', $data, 'admin');
    }
    public function orderStatus(string $id): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $user = Session::get('user');
            $adminId = is_array($user) ? (int)$user['id'] : null;
            (new Order($this->database()))->updateStatus((int)$id, (string)($_POST['status'] ?? ''), $adminId);
            Session::flash('success', 'Đã cập nhật trạng thái đơn hàng.');
            header('Location: /admin/orders');
        } catch (Throwable $exception) {
            http_response_code(422);
            $data = array('title' => 'Đơn hàng');
            $data['error'] = $exception->getMessage();
            $data['orders'] = (new Order($this->database()))->allForAdmin();
            $this->view->render('admin/orders', $data, 'admin');
        }
    }
}
