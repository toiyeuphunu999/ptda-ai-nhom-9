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
            $json = file_get_contents('php://input');
            $payload = json_decode((string)$json, true);
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

    private function showCheckoutError(array $user, string $error): void
    {
        $addressId = (int)($_POST['saved_address_id'] ?? 0);
        $data = $this->getCheckoutModel()->formData((int)$user['id'], $addressId);
        $data['title'] = 'Thanh toán';
        $data['error'] = $error;
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
