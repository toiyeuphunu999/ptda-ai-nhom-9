<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Voucher;
use App\Services\PayOSService;
use RuntimeException;
use Throwable;

final class CartPageController extends Controller
{
    private function books(): Book { return new Book($this->database()); }

    private function cartItems(): array
    {
        $items = [];
        $cart = (array)Session::get('cart', []);
        foreach ($cart as $bookId => $quantity) {
            $book = $this->books()->findById((int)$bookId);
            if (!$book || $book['status'] !== 'published') continue;
            $amount = min((int)$quantity, (int)$book['stock_quantity']);
            if ($amount > 0) $items[] = ['book'=>$book, 'quantity'=>$amount];
        }
        return $items;
    }

    public function cart(): void { Session::start(); $this->view->render('shop/cart', ['title'=>'Giỏ hàng','items'=>$this->cartItems()]); }
    public function checkout(): void
    {
        Session::start(); $user = Session::get('user'); if (!is_array($user)) { header('Location: /login'); return; }
        $userModel = new User($this->database()); $account = $userModel->findById((int)$user['id']) ?: $user;
        $addresses = (new UserAddress($this->database()))->allForUser((int)$user['id']);
        $selectedId = (int)($_GET['address_id'] ?? 0) ?: (int)($addresses[0]['id'] ?? 0);
        foreach ($addresses as $savedAddress) if ((int)$savedAddress['id'] === $selectedId) { $account['name'] = $savedAddress['recipient_name']; $account['phone'] = $savedAddress['phone']; $account['address'] = $savedAddress['address']; break; }
        $data = ['title'=>'Thanh toán','items'=>$this->cartItems(),'paymentMethod'=>$_GET['payment_method'] ?? 'cod','checkoutUser'=>$account,'savedAddresses'=>$addresses,'selectedAddressId'=>$selectedId];
        if (!$userModel->canPurchase((int)$user['id'])) { http_response_code(403); $data['error']='Tài khoản của bạn hiện đang bị tạm khóa quyền mua hàng. Vui lòng liên hệ hỗ trợ.'; }
        $this->view->render('shop/checkout', $data);
    }

    public function placeOrder(): void
    {
        Session::start(); $user = Session::get('user'); if (!is_array($user)) { header('Location: /login'); return; }
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            if (!(new User($this->database()))->canPurchase((int)$user['id'])) throw new RuntimeException('Tài khoản của bạn hiện đang bị tạm khóa quyền mua hàng. Vui lòng liên hệ hỗ trợ.');
            $name = trim((string)($_POST['shipping_name'] ?? '')); $email = trim((string)($_POST['shipping_email'] ?? '')); $phone = trim((string)($_POST['shipping_phone'] ?? '')); $address = trim((string)($_POST['shipping_address'] ?? '')); $method = (string)($_POST['payment_method'] ?? 'cod');
            if (!in_array($method, ['cod','payos'], true)) throw new RuntimeException('Phương thức thanh toán không hợp lệ.');
            if ($name === '' || $phone === '' || $address === '') throw new RuntimeException('Vui lòng nhập đủ thông tin nhận hàng.');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) throw new RuntimeException('Vui lòng nhập email nhận thông báo hợp lệ.');
            $cartItems = $this->cartItems(); $items = []; $subtotal = 0; $payosItems = [];
            foreach ($cartItems as $item) {
                $items[] = ['book_id'=>(int)$item['book']['id'], 'quantity'=>(int)$item['quantity']];
                $subtotal += (int)$item['book']['price'] * (int)$item['quantity'];
                $payosItems[] = ['name'=>$item['book']['title'], 'quantity'=>(int)$item['quantity'], 'price'=>(int)$item['book']['price']];
            }
            $voucherId = null; $voucherCode = trim((string)($_POST['voucher_code'] ?? ''));
            if ($voucherCode !== '') {
                $voucher = (new Voucher($this->database()))->findByCode($voucherCode);
                if (!$voucher || !Voucher::isUsable($voucher, $subtotal)) throw new RuntimeException('Mã giảm giá chưa đủ điều kiện áp dụng cho đơn hàng này.');
                $voucherId = (int)$voucher['id'];
            }
            $orders = new Order($this->database()); $code = $orders->createFromCart((int)$user['id'], $items, $name, $email, $phone, $address, $voucherId, $method === 'payos' ? 'bank_transfer' : 'cod');
            if ($method === 'payos') {
                $order = $orders->findByCode($code); $payos = new PayOSService((new SystemSetting($this->database()))->payOSConfig());
                try {
                    $link = $payos->createPaymentLink((int)$order['id'], (int)$order['total'], 'MB'.(int)$order['id'], $name, $phone, $address, $payosItems);
                    $orders->markPayOSLink((int)$order['id'], (string)$link['data']['paymentLinkId'], $link); header('Location: '.$link['data']['checkoutUrl']); return;
                } catch (Throwable $paymentException) { $orders->cancelForUser((int)$order['id'], (int)$user['id']); throw $paymentException; }
            }
            Session::put('cart', []); Session::flash('success', 'Đặt hàng thành công. Mã đơn: '.$code); header('Location: /orders');
        } catch (Throwable $exception) {
            http_response_code(422); $this->view->render('shop/checkout', ['title'=>'Thanh toán','error'=>$exception->getMessage(),'items'=>$this->cartItems(),'paymentMethod'=>$_POST['payment_method'] ?? 'cod','voucherCode'=>(string)($_POST['voucher_code'] ?? ''),'checkoutUser'=>['name'=>(string)($_POST['shipping_name'] ?? ''),'email'=>(string)($_POST['shipping_email'] ?? ''),'phone'=>(string)($_POST['shipping_phone'] ?? ''),'address'=>(string)($_POST['shipping_address'] ?? '')],'savedAddresses'=>(new UserAddress($this->database()))->allForUser((int)$user['id']),'selectedAddressId'=>(int)($_POST['saved_address_id'] ?? 0)]);
        }
    }

    public function payosReturn(): void { Session::start(); $status=strtoupper((string)($_GET['status']??'PENDING')); Session::flash('success',$status==='PAID'?'Thanh toán PayOS thành công. Đơn hàng đang được xác nhận.':'Đã trở về từ PayOS. Hệ thống đang chờ xác nhận giao dịch.'); header('Location: /orders'); }
    public function payosCancel(): void
    {
        Session::start(); $user=Session::get('user'); $orderCode=filter_var($_GET['orderCode']??null,FILTER_VALIDATE_INT);
        if(is_array($user)&&$orderCode!==false&&$orderCode!==null){try{(new Order($this->database()))->cancelForUser((int)$orderCode,(int)$user['id']);}catch(Throwable){}}
        Session::flash('failure','Bạn đã hủy thanh toán PayOS. Đơn hàng đã được hủy và tồn kho đã được hoàn lại.'); header('Location: /orders');
    }
    public function payosWebhook(): void
    {
        try {
            $payload=json_decode((string)file_get_contents('php://input'),true); $payos=new PayOSService((new SystemSetting($this->database()))->payOSConfig());
            if(!is_array($payload)||!$payos->verifyWebhook($payload)) throw new RuntimeException('Webhook không hợp lệ.');
            $data=$payload['data']; (new Order($this->database()))->markPayOSWebhook((int)$data['orderCode'],(int)$data['amount'],($data['code']??'')==='00'?'PAID':'FAILED',$payload);
            http_response_code(200); header('Content-Type: application/json; charset=utf-8'); echo json_encode(['success'=>true]);
        } catch(Throwable){http_response_code(400);header('Content-Type: application/json; charset=utf-8');echo json_encode(['success'=>false]);}
    }
    public function orderHistory(): void
    {
        Session::start(); $user=Session::get('user'); if(!is_array($user)){header('Location: /login');return;}
        $database=$this->database(); $orders=(new Order($database))->forUser((int)$user['id']); $itemsModel=new OrderItem($database); $orderItems=[];
        foreach($orders as $order) $orderItems[(int)$order['id']]=$itemsModel->forUserOrder((int)$order['id'],(int)$user['id']);
        $this->view->render('shop/order_history',['title'=>'Lịch sử đơn hàng','orders'=>$orders,'orderItems'=>$orderItems]);
    }
    public function add(string $id): void
    {
        Session::start(); try { Csrf::verify($_POST['_csrf']??null); $user=Session::get('user'); if(is_array($user)&&!(new User($this->database()))->canPurchase((int)$user['id'])) throw new RuntimeException('Tài khoản của bạn hiện đang bị tạm khóa quyền mua hàng. Vui lòng liên hệ hỗ trợ.'); $book=$this->books()->findById((int)$id); if(!$book||$book['status']!=='published') throw new RuntimeException('Sách không tồn tại hoặc chưa được bán.'); $cart=(array)Session::get('cart',[]); $current=(int)($cart[$id]??0); if($current+1>(int)$book['stock_quantity']) throw new RuntimeException('Số lượng vượt quá tồn kho.'); $cart[$id]=$current+1; Session::put('cart',$cart); header('Location: /cart'); }
        catch(Throwable $exception){http_response_code(422);$this->view->render('shop/cart',['title'=>'Giỏ hàng','error'=>$exception->getMessage(),'items'=>$this->cartItems()]);}
    }
    public function update(): void
    {
        Session::start(); Csrf::verify($_POST['_csrf']??null); $cart=(array)Session::get('cart',[]); if(isset($_POST['remove_id'])) unset($cart[(string)(int)$_POST['remove_id']]); else foreach((array)($_POST['quantity']??[]) as $id=>$quantity){$book=$this->books()->findById((int)$id);$amount=max(0,(int)$quantity);if(!$book||$book['status']!=='published'||$amount===0)unset($cart[$id]);else $cart[$id]=min($amount,(int)$book['stock_quantity']);} Session::put('cart',$cart); header('Location: /cart');
    }
    public function remove(string $id): void { Session::start(); Csrf::verify($_POST['_csrf']??null); $cart=(array)Session::get('cart',[]); unset($cart[$id]); Session::put('cart',$cart); header('Location: /cart'); }
}
