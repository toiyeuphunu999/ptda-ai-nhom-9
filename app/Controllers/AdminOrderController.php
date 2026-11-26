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

    public function detail(string $id): void
    {
        $this->authorize();
        $order = (new Order($this->database()))->detailForAdmin((int)$id);
        if (!$order) {
            http_response_code(404);
            $this->view->render('errors/error', ['title' => 'Không tìm thấy đơn hàng', 'errorCode' => '404', 'errorTitle' => 'Không tìm thấy đơn hàng', 'errorMessage' => 'Đơn hàng không tồn tại hoặc đã bị xóa.', 'errorLabel' => 'Quay lại danh sách đơn hàng.', 'primaryUrl' => '/admin/orders', 'primaryLabel' => 'Danh sách đơn hàng'], 'admin');
            return;
        }
        $this->view->render('admin/order_detail', ['title' => 'Chi tiết đơn hàng', 'order' => $order], 'admin');
    }

    public function orderStatus(string $id): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $user = Session::get('user');
            $adminId = is_array($user) ? (int)$user['id'] : null;
            (new Order($this->database()))->updateStatus((int)$id, (string)($_POST['status'] ?? ''), $adminId, trim((string)($_POST['reason'] ?? '')));
            Session::flash('success', 'Đã cập nhật trạng thái đơn hàng.');
            header('Location: /admin/orders/' . (int)$id);
        } catch (Throwable $exception) {
            http_response_code(422);
            $data = array('title' => 'Đơn hàng');
            $data['error'] = $exception->getMessage();
            $order = (new Order($this->database()))->detailForAdmin((int)$id);
            if ($order) {
                $this->view->render('admin/order_detail', ['title' => 'Chi tiết đơn hàng', 'order' => $order, 'error' => $exception->getMessage()], 'admin');
            } else {
                $data['orders'] = (new Order($this->database()))->allForAdmin();
                $this->view->render('admin/orders', $data, 'admin');
            }
        }
    }

    public function cancelDecision(string $id): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $user = Session::get('user');
            $adminId = is_array($user) ? (int)$user['id'] : 0;
            $approve = ($_POST['decision'] ?? '') === 'approve';
            (new Order($this->database()))->reviewCancel((int)$id, $approve, $adminId, (string)($_POST['reason'] ?? ''));
            Session::flash('success', $approve ? 'Đã chấp nhận hủy đơn.' : 'Đã từ chối yêu cầu hủy đơn.');
            header('Location: /admin/orders/' . (int)$id);
        } catch (Throwable $exception) {
            http_response_code(422);
            $order = (new Order($this->database()))->detailForAdmin((int)$id);
            $this->view->render('admin/order_detail', ['title' => 'Chi tiết đơn hàng', 'order' => $order, 'error' => $exception->getMessage()], 'admin');
        }
    }

    public function returnDecision(string $id): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $user = Session::get('user');
            $adminId = is_array($user) ? (int)$user['id'] : 0;
            $approve = ($_POST['decision'] ?? '') === 'approve';
            (new Order($this->database()))->reviewReturn((int)$id, $approve, $adminId, (string)($_POST['reason'] ?? ''));
            Session::flash('success', $approve ? 'Đã chấp nhận yêu cầu đổi trả.' : 'Đã từ chối yêu cầu đổi trả.');
            header('Location: /admin/orders/' . (int)$id);
        } catch (Throwable $exception) {
            http_response_code(422);
            $order = (new Order($this->database()))->detailForAdmin((int)$id);
            $this->view->render('admin/order_detail', ['title' => 'Chi tiết đơn hàng', 'order' => $order, 'error' => $exception->getMessage()], 'admin');
        }
    }

    public function returnReceived(string $id): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $user = Session::get('user');
            $adminId = is_array($user) ? (int)$user['id'] : 0;
            (new Order($this->database()))->confirmReturned((int)$id, $adminId, (string)($_POST['reason'] ?? ''));
            Session::flash('success', 'Đã xác nhận nhận được hàng trả.');
            header('Location: /admin/orders/' . (int)$id);
        } catch (Throwable $exception) {
            http_response_code(422);
            $order = (new Order($this->database()))->detailForAdmin((int)$id);
            $this->view->render('admin/order_detail', ['title' => 'Chi tiết đơn hàng', 'order' => $order, 'error' => $exception->getMessage()], 'admin');
        }
    }

    public function returnImage(string $id): void
    {
        $this->authorize();
        $order = (new Order($this->database()))->findById((int)$id);
        $path = $order ? dirname(__DIR__, 2) . '/storage/returns/' . basename((string)($order['return_image'] ?? '')) : '';
        if (!$order || $path === '' || !is_file($path)) { http_response_code(404); return; }
        $mime = mime_content_type($path) ?: 'application/octet-stream';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) { http_response_code(404); return; }
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="return-image"');
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }
}
