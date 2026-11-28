<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Cart;
use App\Models\Checkout;
use App\Models\Order;
use App\Models\User;
use Throwable;

class CartPageController extends Controller
{
    private function getCartModel(): Cart
    {
        return new Cart($this->database());
    }

    private function getCheckoutModel(): Checkout
    {
        return new Checkout($this->database());
    }

    public function cart(): void
    {
        Session::start();
        $data = array();
        $data['title'] = 'Giỏ hàng';
        $data['items'] = $this->getCartModel()->items();
        $this->view->render('shop/cart', $data);
    }

    public function checkout(): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) {
            header('Location: /login');
            return;
        }

        $userId = (int)$user['id'];
        $addressId = (int)($_GET['address_id'] ?? 0);
        $data = $this->getCheckoutModel()->formData($userId, $addressId);
        $data['title'] = 'Thanh toán';
        $data['items'] = $this->getCartModel()->items();
        $data['paymentMethod'] = $_GET['payment_method'] ?? 'cod';

        $userModel = new User($this->database());
        if (!$userModel->canPurchase($userId)) {
            http_response_code(403);
            $data['error'] = 'Tài khoản của bạn hiện đang bị tạm khóa quyền mua hàng.';
        }
        $this->view->render('shop/checkout', $data);
    }

    public function placeOrder(): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) {
            header('Location: /login');
            return;
        }

        try {
            Csrf::verify($_POST['_csrf'] ?? null);

            if (($_POST['checkout_action'] ?? '') === 'apply_voucher') {
                $voucher = $this->getCheckoutModel()->validateVoucherCode(
                    (string)($_POST['voucher_code'] ?? ''),
                    $this->getCartModel()->items()
                );
                $this->showCheckoutError(
                    $user,
                    '',
                    'Đã áp dụng mã ' . $voucher['code'] . ' — giảm ' . number_format((int)$voucher['discount'], 0, ',', '.') . ' đ.'
                );
                return;
            }

            $result = $this->getCheckoutModel()->place((int)$user['id'], $_POST);

            if (!empty($result['paymentUrl'])) {
                header('Location: ' . $result['paymentUrl']);
                return;
            }

            Session::flash('success', 'Đặt hàng thành công. Mã đơn: ' . $result['orderCode']);
            header('Location: /orders');
        } catch (Throwable $exception) {
            http_response_code(422);
            $this->showCheckoutError($user, $exception->getMessage());
        }
    }

    public function payosReturn(): void
    {
        Session::start();
        $status = strtoupper((string)($_GET['status'] ?? 'PENDING'));
        if ($status === 'PAID') {
            Session::flash('success', 'Thanh toán PayOS thành công. Đơn hàng đang được xác nhận.');
        } else {
            Session::flash('success', 'Đã trở về từ PayOS. Hệ thống đang chờ xác nhận giao dịch.');
        }
        header('Location: /orders');
    }

    public function payosCancel(): void
    {
        Session::start();
        $user = Session::get('user');
        $orderCode = filter_var($_GET['orderCode'] ?? null, FILTER_VALIDATE_INT);

        if (is_array($user) && $orderCode !== false && $orderCode !== null) {
            try {
                $this->getCheckoutModel()->cancelPayOS((int)$orderCode, (int)$user['id']);
            } catch (Throwable $exception) {
                // Vẫn chuyển người dùng về lịch sử đơn hàng nếu PayOS gửi lại nhiều lần.
            }
        }
        Session::flash('failure', 'Bạn đã hủy thanh toán PayOS.');
        header('Location: /orders');
    }

    public function payosWebhook(): void
    {
        try {
            if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 1024 * 1024) {
                throw new \RuntimeException('Webhook quá lớn.');
            }
            $json = file_get_contents('php://input');
            $payload = json_decode((string)$json, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($payload)) {
                throw new \RuntimeException('Webhook không hợp lệ.');
            }
            $this->getCheckoutModel()->webhook($payload);
            http_response_code(200);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('success' => true));
        } catch (Throwable $exception) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('success' => false));
        }
    }

    public function orderHistory(): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) {
            header('Location: /login');
            return;
        }

        $orders = (new Order($this->database()))->forUserWithItems((int)$user['id']);
        $orderItems = array();
        foreach ($orders as $order) {
            $orderItems[(int)$order['id']] = $order['items'] ?? array();
        }

        $data = array();
        $data['title'] = 'Lịch sử đơn hàng';
        $data['orders'] = $orders;
        $data['orderItems'] = $orderItems;
        $this->view->render('shop/order_history', $data);
    }

    public function orderDetail(string $id): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) {
            header('Location: /login');
            return;
        }

        $order = (new Order($this->database()))->detailForUser((int)$id, (int)$user['id']);
        if (!$order) {
            http_response_code(404);
            $this->view->render('errors/error', array(
                'title' => 'Không tìm thấy đơn hàng',
                'errorCode' => '404',
                'errorTitle' => 'Không tìm thấy đơn hàng',
                'errorMessage' => 'Đơn hàng không tồn tại.',
                'errorLabel' => 'Quay lại lịch sử đơn hàng.',
                'primaryUrl' => '/orders',
                'primaryLabel' => 'Lịch sử đơn hàng',
            ));
            return;
        }

        $data = array(
            'title' => 'Chi tiết đơn hàng',
            'order' => $order,
        );
        $this->view->render('shop/order_detail', $data);
    }

    public function cancelOrder(string $id): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) {
            header('Location: /login');
            return;
        }

        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            (new Order($this->database()))->cancelByCustomer((int)$id, (int)$user['id'], (string)($_POST['reason'] ?? ''));
            Session::flash('success', 'Đã gửi xử lý hủy đơn hàng.');
        } catch (Throwable $exception) {
            Session::flash('failure', $exception->getMessage());
        }

        header('Location: /orders/' . (int)$id);
    }

    public function confirmReceived(string $id): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) {
            header('Location: /login');
            return;
        }

        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            (new Order($this->database()))->confirmReceived((int)$id, (int)$user['id']);
            Session::flash('success', 'Đã xác nhận nhận được hàng. Đơn hàng đã hoàn tất.');
        } catch (Throwable $exception) {
            Session::flash('failure', $exception->getMessage());
        }

        header('Location: /orders/' . (int)$id);
    }

    public function requestReturn(string $id): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) {
            header('Location: /login');
            return;
        }
        $image = null;
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $image = $this->saveReturnImage($_FILES['return_image'] ?? array());
            (new Order($this->database()))->requestReturn((int)$id, (int)$user['id'], (string)($_POST['reason'] ?? ''), $image);
            Session::flash('success', 'Đã gửi yêu cầu đổi trả để nhà sách kiểm tra.');
        } catch (Throwable $exception) {
            if ($image !== null) {
                $savedPath = dirname(__DIR__, 2) . '/storage/returns/' . basename($image);
                if (is_file($savedPath)) unlink($savedPath);
            }
            Session::flash('failure', $exception->getMessage());
        }
        header('Location: /orders/' . (int)$id);
    }

    private function saveReturnImage(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $temporaryPath = (string)($file['tmp_name'] ?? '');
        $fileError = (int)($file['error'] ?? UPLOAD_ERR_OK);
        $fileSize = (int)($file['size'] ?? 0);
        if ($fileError !== UPLOAD_ERR_OK || !is_uploaded_file($temporaryPath) || $fileSize < 1 || $fileSize > 5 * 1024 * 1024) {
            throw new \RuntimeException('Ảnh đổi trả không hợp lệ hoặc vượt quá 5MB.');
        }

        $mime = (string)(mime_content_type($temporaryPath) ?: '');
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mime])) {
            throw new \RuntimeException('Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.');
        }

        $directory = dirname(__DIR__, 2) . '/storage/returns';
        if (!is_dir($directory) && !mkdir($directory, 0750, true)) {
            throw new \RuntimeException('Không thể tạo thư mục lưu ảnh đổi trả.');
        }

        $name = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file((string)$file['tmp_name'], $directory . '/' . $name)) {
            throw new \RuntimeException('Không thể lưu ảnh đổi trả.');
        }

        return $name;
    }

    public function add(string $id): void
    {
        Session::start();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $user = Session::get('user');
            if (is_array($user)) {
                $userModel = new User($this->database());
                if (!$userModel->canPurchase((int)$user['id'])) {
                    throw new \RuntimeException('Tài khoản của bạn hiện đang bị tạm khóa quyền mua hàng.');
                }
            }
            $this->getCartModel()->add((int)$id);
            header('Location: /cart');
        } catch (Throwable $exception) {
            http_response_code(422);
            $data = array();
            $data['title'] = 'Giỏ hàng';
            $data['error'] = $exception->getMessage();
            $data['items'] = $this->getCartModel()->items();
            $this->view->render('shop/cart', $data);
        }
    }

    public function update(): void
    {
        Session::start();
        Csrf::verify($_POST['_csrf'] ?? null);
        $quantities = (array)($_POST['quantity'] ?? array());
        $removeId = isset($_POST['remove_id']) ? (int)$_POST['remove_id'] : null;
        $this->getCartModel()->update($quantities, $removeId);
        header('Location: /cart');
    }

    public function remove(string $id): void
    {
        Session::start();
        Csrf::verify($_POST['_csrf'] ?? null);
        $this->getCartModel()->remove((int)$id);
        header('Location: /cart');
    }

    private function showCheckoutError(array $user, string $error, string $success = ''): void
    {
        $addressId = (int)($_POST['saved_address_id'] ?? 0);
        $data = $this->getCheckoutModel()->formData((int)$user['id'], $addressId);
        $data['title'] = 'Thanh toán';
        $data['error'] = $error;
        $data['success'] = $success;
        $data['items'] = $this->getCartModel()->items();
        $data['paymentMethod'] = $_POST['payment_method'] ?? 'cod';
        $data['voucherCode'] = (string)($_POST['voucher_code'] ?? '');
        $data['checkoutUser'] = array(
            'name' => (string)($_POST['shipping_name'] ?? ''),
            'email' => (string)($_POST['shipping_email'] ?? ''),
            'phone' => (string)($_POST['shipping_phone'] ?? ''),
            'address' => (string)($_POST['shipping_address'] ?? ''),
        );
        $this->view->render('shop/checkout', $data);
    }
}
