<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Session;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Voucher;
use App\Models\SystemSetting;
use App\Services\PayOSService;
use Throwable;

final class CartPageController extends Controller
{
    private function books(): Book { return new Book(new Database(require dirname(__DIR__, 2) . '/config/database.php')); }
    private function payOS(Database $database): PayOSService { return new PayOSService((new SystemSetting($database))->payOSConfig()); }

    private function cartItems(): array
    {
        $cart = Session::get('cart', []);
        $items = [];
        foreach ($cart as $bookId => $quantity) {
            $book = $this->books()->findById((int)$bookId);
            if (!$book || $book['status'] !== 'published') {
                continue;
            }
            $items[] = ['book'=>$book, 'quantity'=>min((int)$quantity, (int)$book['stock_quantity'])];
        }
        return array_values(array_filter($items, fn(array $item): bool => $item['quantity'] > 0));
    }

    public function cart(): void { Session::start(); $this->view->render('shop/cart', ['title'=>'Giỏ hàng','items'=>$this->cartItems()]); }
    public function checkout(): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) { header('Location: /login'); return; }
        $this->view->render('shop/checkout', ['title'=>'Thanh toán','items'=>$this->cartItems(), 'paymentMethod'=>$_GET['payment_method'] ?? 'cod']);
    }

    public function placeOrder(): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) { header('Location: /login'); return; }
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $name = trim((string)($_POST['shipping_name'] ?? ''));
            $phone = trim((string)($_POST['shipping_phone'] ?? ''));
            $address = trim((string)($_POST['shipping_address'] ?? ''));
            $paymentMethod = (string)($_POST['payment_method'] ?? 'cod');
            if (!in_array($paymentMethod, ['cod', 'payos'], true)) throw new \RuntimeException('Phương thức thanh toán không hợp lệ.');
            if ($name === '' || $phone === '' || $address === '') throw new \RuntimeException('Vui lòng nhập đủ thông tin nhận hàng.');
            $items = array_map(fn(array $item): array => ['book_id'=>(int)$item['book']['id'], 'quantity'=>(int)$item['quantity']], $this->cartItems());
            $voucherId = null;
            $voucherCode = trim((string)($_POST['voucher_code'] ?? ''));
            if ($voucherCode !== '') {
                $subtotal = array_reduce($this->cartItems(), fn(int $sum, array $item): int => $sum + (int)$item['book']['price'] * (int)$item['quantity'], 0);
                $voucherModel = new Voucher(new Database(require dirname(__DIR__, 2) . '/config/database.php'));
                $voucher = $voucherModel->findByCode($voucherCode);
                if (!$voucher) throw new \RuntimeException('Mã giảm giá không tồn tại.');
                if (!Voucher::isUsable($voucher, $subtotal)) throw new \RuntimeException('Mã giảm giá chưa đủ điều kiện áp dụng cho đơn hàng này.');
                $voucherId = (int)$voucher['id'];
            }
            $orderModel = new Order(new Database(require dirname(__DIR__, 2) . '/config/database.php'));
            $code = $orderModel->createFromCart((int)$user['id'], $items, $name, $phone, $address, $voucherId, $paymentMethod === 'payos' ? 'bank_transfer' : 'cod');
            if ($paymentMethod === 'payos') {
                $order = $orderModel->findByCode($code);
                $payos = $this->payOS(new Database(require dirname(__DIR__, 2) . '/config/database.php'));
                try {
                    $link = $payos->createPaymentLink(
                        (int)$order['id'], (int)$order['total'], 'MB' . (int)$order['id'], $name, $phone, $address,
                        array_map(static fn(array $item): array => ['name'=>$item['book']['title'], 'quantity'=>(int)$item['quantity'], 'price'=>(int)$item['book']['price']], $this->cartItems())
                    );
                    $orderModel->markPayOSLink((int)$order['id'], (string)$link['data']['paymentLinkId'], $link);
                    header('Location: ' . $link['data']['checkoutUrl']);
                    return;
                } catch (Throwable $paymentException) {
                    $orderModel->cancelForUser((int)$order['id'], (int)$user['id']);
                    throw $paymentException;
                }
            }
            Session::put('cart', []);
            Session::flash('success', 'Đặt hàng thành công. Mã đơn: ' . $code);
            header('Location: /orders');
        } catch (Throwable $exception) {
            http_response_code(422);
            $this->view->render('shop/checkout', ['title'=>'Thanh toán','error'=>$exception->getMessage(),'items'=>$this->cartItems(), 'paymentMethod'=>$_POST['payment_method'] ?? 'cod', 'voucherCode'=>(string)($_POST['voucher_code'] ?? '')]);
        }
    }

    public function payosReturn(): void
    {
        Session::start();
        $status = strtoupper((string)($_GET['status'] ?? 'PENDING'));
        $message = $status === 'PAID' ? 'Thanh toán PayOS thành công. Đơn hàng đang được xác nhận.' : 'Đã trở về từ PayOS. Hệ thống đang chờ xác nhận giao dịch.';
        Session::flash('success', $message);
        header('Location: /orders');
    }

    public function payosCancel(): void
    {
        Session::start();
        $user = Session::get('user');
        $orderCode = filter_var($_GET['orderCode'] ?? null, FILTER_VALIDATE_INT);
        if (is_array($user) && $orderCode !== false && $orderCode !== null) {
            try {
                (new Order(new Database(require dirname(__DIR__, 2) . '/config/database.php')))->cancelForUser((int)$orderCode, (int)$user['id']);
            } catch (Throwable) {
                // Không tin dữ liệu redirect để tự ý đổi trạng thái đơn đã thanh toán.
            }
        }
        Session::flash('failure', 'Bạn đã hủy thanh toán PayOS. Đơn hàng đã được hủy và tồn kho đã được hoàn lại.');
        header('Location: /orders');
    }

    public function payosWebhook(): void
    {
        $payload = json_decode((string)file_get_contents('php://input'), true);
        $payos = $this->payOS(new Database(require dirname(__DIR__, 2) . '/config/database.php'));
        if (!is_array($payload) || !$payos->verifyWebhook($payload)) { http_response_code(400); echo json_encode(['success'=>false]); return; }
        try {
            $data = $payload['data'];
            (new Order(new Database(require dirname(__DIR__, 2) . '/config/database.php')))->markPayOSWebhook((int)$data['orderCode'], (int)$data['amount'], (string)($data['code'] === '00' ? 'PAID' : 'FAILED'), $payload);
            http_response_code(200);
            echo json_encode(['success'=>true]);
        } catch (Throwable $exception) { http_response_code(400); echo json_encode(['success'=>false]); }
    }

    public function orderHistory(): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) { header('Location: /login'); return; }
        $database = new Database(require dirname(__DIR__, 2) . '/config/database.php');
        $orders = (new Order($database))->forUser((int)$user['id']);
        $orderItems = [];
        $itemModel = new OrderItem($database);
        foreach ($orders as $order) {
            $orderItems[(int)$order['id']] = $itemModel->forUserOrder((int)$order['id'], (int)$user['id']);
        }
        $this->view->render('shop/order_history', ['title'=>'Lịch sử đơn hàng','orders'=>$orders,'orderItems'=>$orderItems]);
    }

    public function add(string $id): void
    {
        Session::start();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $book = $this->books()->findById((int)$id);
            if (!$book || $book['status'] !== 'published') {
                throw new \RuntimeException('Sách không tồn tại hoặc chưa được bán.');
            }
            $cart = Session::get('cart', []);
            $current = (int)($cart[$id] ?? 0);
            if ($current + 1 > (int)$book['stock_quantity']) {
                throw new \RuntimeException('Số lượng vượt quá tồn kho.');
            }
            $cart[$id] = $current + 1;
            Session::put('cart', $cart);
            header('Location: /cart');
        } catch (Throwable $exception) {
            http_response_code(422);
            $this->view->render('shop/cart', ['title'=>'Giỏ hàng','error'=>$exception->getMessage(),'items'=>$this->cartItems()]);
        }
    }

    public function update(): void
    {
        Session::start();
        Csrf::verify($_POST['_csrf'] ?? null);
        $cart = Session::get('cart', []);
        if (isset($_POST['remove_id'])) {
            unset($cart[(string)(int)$_POST['remove_id']]);
            Session::put('cart', $cart);
            header('Location: /cart');
            return;
        }
        foreach ((array)($_POST['quantity'] ?? []) as $id => $quantity) {
            $book = $this->books()->findById((int)$id);
            $amount = max(0, (int)$quantity);
            if (!$book || $book['status'] !== 'published' || $amount === 0) {
                unset($cart[$id]);
                continue;
            }
            $cart[$id] = min($amount, (int)$book['stock_quantity']);
        }
        Session::put('cart', $cart);
        header('Location: /cart');
    }

    public function remove(string $id): void
    {
        Session::start();
        Csrf::verify($_POST['_csrf'] ?? null);
        $cart = Session::get('cart', []);
        unset($cart[$id]);
        Session::put('cart', $cart);
        header('Location: /cart');
    }
}
