<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Services\PayOSService;
use RuntimeException;

class Checkout
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function formData(int $userId, int $selectedAddressId): array
    {
        $userModel = new User($this->database);
        $addressModel = new UserAddress($this->database);
        $account = $userModel->findById($userId);
        $addresses = $addressModel->allForUser($userId);

        if ($selectedAddressId === 0 && count($addresses) > 0) {
            $selectedAddressId = (int)$addresses[0]['id'];
        }

        foreach ($addresses as $address) {
            if ((int)$address['id'] === $selectedAddressId) {
                $account['name'] = $address['recipient_name'];
                $account['phone'] = $address['phone'];
                $account['address'] = $address['address'];
                break;
            }
        }

        return array(
            'checkoutUser' => $account,
            'savedAddresses' => $addresses,
            'selectedAddressId' => $selectedAddressId,
        );
    }

    public function place(int $userId, array $input): array
    {
        $userModel = new User($this->database);
        if (!$userModel->canPurchase($userId)) {
            throw new RuntimeException('Tài khoản hiện không được phép mua hàng.');
        }

        $name = trim((string)($input['shipping_name'] ?? ''));
        $email = trim((string)($input['shipping_email'] ?? ''));
        $phone = trim((string)($input['shipping_phone'] ?? ''));
        $address = trim((string)($input['shipping_address'] ?? ''));
        $paymentMethod = (string)($input['payment_method'] ?? 'cod');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || $address === '') {
            throw new RuntimeException('Vui lòng nhập đầy đủ thông tin nhận hàng.');
        }
        if ($paymentMethod !== 'cod' && $paymentMethod !== 'bank_transfer') {
            throw new RuntimeException('Phương thức thanh toán không hợp lệ.');
        }

        $cartItems = (new Cart($this->database))->items();
        if (count($cartItems) === 0) {
            throw new RuntimeException('Giỏ hàng đang trống.');
        }

        $orderItems = array();
        $payosItems = array();
        foreach ($cartItems as $item) {
            $book = $item['book'];
            $quantity = (int)$item['quantity'];
            $orderItems[] = array('book_id' => (int)$book['id'], 'quantity' => $quantity);
            $payosItems[] = array(
                'name' => $book['title'],
                'quantity' => $quantity,
                'price' => (int)$book['price'],
            );
        }

        $voucherId = $this->getVoucherId((string)($input['voucher_code'] ?? ''), $cartItems);
        $orderModel = new Order($this->database);
        $orderCode = $orderModel->createFromCart(
            $userId,
            $orderItems,
            $name,
            $email,
            $phone,
            $address,
            $voucherId,
            $paymentMethod
        );

        if ($paymentMethod === 'bank_transfer') {
            $order = $orderModel->findByCode($orderCode);
            if (!$order) {
                throw new RuntimeException('Không tìm thấy đơn hàng vừa tạo.');
            }

            try {
                $settings = new SystemSetting($this->database);
                $payos = new PayOSService($settings->payOSConfig());
                $payment = $payos->createPaymentLink(
                    (int)$order['id'],
                    (int)$order['total'],
                    'MB' . (int)$order['id'],
                    $name,
                    $phone,
                    $address,
                    $payosItems
                );
                $paymentLinkId = (string)$payment['data']['paymentLinkId'];
                $orderModel->markPayOSLink((int)$order['id'], $paymentLinkId, $payment);
                return array(
                    'paymentUrl' => $payment['data']['checkoutUrl'],
                    'orderCode' => $orderCode,
                );
            } catch (\Throwable $exception) {
                $orderModel->cancelForUser((int)$order['id'], $userId);
                throw $exception;
            }
        }

        (new Cart($this->database))->clear();
        return array('orderCode' => $orderCode);
    }

    public function cancelPayOS(int $orderCode, int $userId): void
    {
        $orderModel = new Order($this->database);
        $order = $orderModel->findByCode((string)$orderCode);
        if ($order && (int)$order['user_id'] === $userId) {
            $orderModel->cancelForUser((int)$order['id'], $userId);
        }
    }

    public function webhook(array $payload): void
    {
        $settings = new SystemSetting($this->database);
        $payos = new PayOSService($settings->payOSConfig());
        if (!$payos->verifyWebhook($payload)) {
            throw new RuntimeException('Webhook PayOS không hợp lệ.');
        }

        $data = $payload['data'] ?? array();
        $orderCode = (string)($data['orderCode'] ?? '');
        $order = (new Order($this->database))->findByCode($orderCode);
        if (!$order) {
            throw new RuntimeException('Không tìm thấy đơn hàng.');
        }

        (new Order($this->database))->markPayOSWebhook(
            (int)$order['id'],
            (int)($data['amount'] ?? 0),
            (string)($data['code'] ?? $payload['code'] ?? 'FAILED'),
            $payload
        );
    }

    private function getVoucherId(string $voucherCode, array $cartItems): ?int
    {
        $voucherCode = trim($voucherCode);
        if ($voucherCode === '') {
            return null;
        }

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += (int)$item['book']['price'] * (int)$item['quantity'];
        }

        $voucher = (new Voucher($this->database))->findByCode($voucherCode);
        if (!$voucher || !Voucher::isUsable($voucher, $subtotal)) {
            throw new RuntimeException('Mã giảm giá không hợp lệ hoặc đã hết hạn.');
        }
        return (int)$voucher['id'];
    }
}
