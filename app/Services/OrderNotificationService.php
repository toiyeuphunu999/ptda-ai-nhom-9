<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Notification;

final class OrderNotificationService
{
    private const STATUS_LABELS = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao hàng',
        'completed' => 'Đã hoàn thành',
        'cancelled' => 'Đã hủy'
    ];

    public function __construct(
        private Database $database
    ) {
    }

    // Thông báo khi khách hàng đặt hàng thành công
    public function orderPlaced(string $orderCode): void
    {
        $order = $this->findOrder($orderCode);

        if (!$order) {
            return;
        }

        $message = 'Đơn hàng ' . $orderCode .
            ' đã được tiếp nhận thành công.';

        // Thông báo trong tài khoản
        $this->createNotification(
            (int)$order['user_id'],
            (int)$order['id'],
            'Đặt hàng thành công',
            $message
        );

        // Gửi email
        $this->sendOrderEmail(
            $order,
            'Xác nhận đơn hàng ' . $orderCode,
            'Cảm ơn bạn đã đặt hàng tại BOOKSTORE!',
            $message
        );
    }

    // Thông báo khi đơn hàng thay đổi trạng thái
    public function statusChanged(
        int $orderId,
        string $newStatus
    ): void {
        $sql = "SELECT
                    orders.*,
                    users.email,
                    users.name,
                    users.id AS customer_id
                FROM orders
                INNER JOIN users
                    ON users.id = orders.user_id
                WHERE orders.id = :id
                LIMIT 1";

        $stmt = $this->database->connection()->prepare($sql);

        $stmt->execute([
            'id' => $orderId
        ]);

        $order = $stmt->fetch();

        if (!$order) {
            return;
        }

        $statusName = self::STATUS_LABELS[$newStatus]
            ?? $newStatus;

        $message = 'Đơn hàng ' .
            $order['order_code'] .
            ' hiện có trạng thái: ' .
            $statusName . '.';

        // Tạo thông báo trong tài khoản
        $this->createNotification(
            (int)$order['customer_id'],
            $orderId,
            'Cập nhật trạng thái đơn hàng',
            $message
        );

        // Gửi email
        $this->sendOrderEmail(
            $order,
            'Thông báo đơn hàng ' . $order['order_code'],
            'Đơn hàng của bạn đã được cập nhật!',
            $message
        );
    }

    // Lấy đơn hàng
    private function findOrder(string $orderCode): ?array
    {
        $sql = "SELECT
                    orders.*,
                    users.email,
                    users.name,
                    users.id AS user_id
                FROM orders
                INNER JOIN users
                    ON users.id = orders.user_id
                WHERE orders.order_code = :code
                LIMIT 1";

        $stmt = $this->database->connection()->prepare($sql);

        $stmt->execute([
            'code' => $orderCode
        ]);

        return $stmt->fetch() ?: null;
    }

    // Tạo thông báo
    private function createNotification(
        int $userId,
        int $orderId,
        string $title,
        string $message
    ): void {
        $notification = new Notification($this->database);

        $notification->create(
            $userId,
            $orderId,
            $title,
            $message
        );
    }

    // Gửi email
    private function sendOrderEmail(
        array $order,
        string $subject,
        string $heading,
        string $message
    ): bool {
        $email = (string)($order['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $from = getenv('MAIL_FROM') ?: '';

        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $customerName = $this->escape(
            (string)($order['name'] ?? $order['shipping_name'] ?? 'Khách hàng')
        );

        $orderCode = $this->escape(
            (string)$order['order_code']
        );

        $shippingName = $this->escape(
            (string)$order['shipping_name']
        );

        $shippingAddress = $this->escape(
            (string)$order['shipping_address']
        );

        $total = number_format(
            (int)$order['total'],
            0,
            ',',
            '.'
        );

        $html = '
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
        </head>

        <body style="
            font-family:Arial,sans-serif;
            background:#f5f5f5;
            padding:25px;
            color:#333;
        ">

            <div style="
                max-width:600px;
                margin:auto;
                padding:30px;
                background:#ffffff;
                border-radius:10px;
            ">

                <h2 style="color:#7b4b2a;">
                    BOOKSTORE
                </h2>

                <h3>' . $heading . '</h3>

                <p>Xin chào ' . $customerName . '!</p>

                <p>' . $this->escape($message) . '</p>

                <hr>

                <p>
                    <strong>Mã đơn hàng:</strong>
                    ' . $orderCode . '
                </p>

                <p>
                    <strong>Người nhận:</strong>
                    ' . $shippingName . '
                </p>

                <p>
                    <strong>Địa chỉ:</strong>
                    ' . $shippingAddress . '
                </p>

                <p>
                    <strong>Tổng tiền:</strong>
                    ' . $total . ' VNĐ
                </p>

                <hr>

                <p>
                    Cảm ơn bạn đã mua sắm tại BOOKSTORE!
                </p>

            </div>

        </body>
        </html>';

        $encodedSubject = '=?UTF-8?B?' .
            base64_encode($subject) . '?=';

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: BOOKSTORE <' . $from . '>',
            'Reply-To: ' . $from,
            'X-Mailer: PHP/' . PHP_VERSION
        ];

        return mail(
            $email,
            $encodedSubject,
            $html,
            implode("\r\n", $headers)
        );
    }

    // Chống lỗi HTML
    private function escape(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}