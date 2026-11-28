<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\EmailTemplate;
use App\Core\SmtpMailer;
use RuntimeException;
use App\Models\Voucher;

final class Order
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function findByCode(string $orderCode): ?array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT * FROM orders WHERE order_code = :order_code LIMIT 1'
        );
        $statement->execute(['order_code' => $orderCode]);
        $order = $statement->fetch();
        return $order ?: null;
    }

    public function findById(int $orderId): ?array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT * FROM orders WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $orderId]);
        $order = $statement->fetch();
        return $order ?: null;
    }

    public function createFromCart(int $userId, array $items, string $name, string $email, string $phone, string $address, ?int $voucherId = null, string $paymentMethod = 'cod'): string
    {
        if ($userId < 1 || $name === '' || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190 || !preg_match('/^[0-9+().\s-]{8,30}$/', $phone) || $address === '' || mb_strlen($address) > 255) {
            throw new RuntimeException('Thông tin nhận hàng không hợp lệ.');
        }
        if (!in_array($paymentMethod, ['cod', 'bank_transfer'], true)) {
            throw new RuntimeException('Phương thức thanh toán không hợp lệ.');
        }
        $databaseConnection = $this->database->connection();
        $databaseConnection->beginTransaction();
        try {
            $subtotal = 0;
            $lockedItems = [];
            foreach ($items as $item) {
                $statement = $databaseConnection->prepare('SELECT * FROM books WHERE id = :id AND status = :status FOR UPDATE');
                $statement->execute(['id' => (int)$item['book_id'], 'status' => 'published']);
                $book = $statement->fetch();
                $quantity = (int)$item['quantity'];
                if (!$book || $quantity < 1 || $quantity > (int)$book['stock_quantity']) {
                    throw new RuntimeException('Sách không đủ tồn kho để đặt hàng.');
                }
                $lineTotal = (int)$book['price'] * $quantity;
                $subtotal += $lineTotal;
                $lockedItems[] = ['book' => $book, 'quantity' => $quantity, 'line_total' => $lineTotal];
            }
            if ($lockedItems === []) {
                throw new RuntimeException('Giỏ hàng đang trống.');
            }

            $discount = 0;
            $voucherCode = null;
            if ($voucherId !== null) {
                $voucherStatement = $databaseConnection->prepare('SELECT * FROM vouchers WHERE id = :id FOR UPDATE');
                $voucherStatement->execute(['id' => $voucherId]);
                $voucher = $voucherStatement->fetch();
                if (!$voucher) {
                    throw new RuntimeException('Mã giảm giá không tồn tại.');
                }
                $discount = Voucher::discountFor($voucher, $subtotal);
                $voucherCode = $voucher['code'];
                $databaseConnection->prepare('UPDATE vouchers SET used_count = used_count + 1 WHERE id = :id')->execute(['id' => $voucherId]);
            }
            $total = max(0, $subtotal - $discount);

            $orderCode = 'MB' . date('ymdHis') . strtoupper(bin2hex(random_bytes(3)));
            $orderStatement = $databaseConnection->prepare(
                'INSERT INTO orders (user_id, order_code, status, subtotal, shipping_fee, discount_amount, voucher_code, total, shipping_name, shipping_email, shipping_phone, shipping_address) '
                . 'VALUES (:user_id, :order_code, :status, :subtotal, :shipping_fee, :discount_amount, :voucher_code, :total, :shipping_name, :shipping_email, :shipping_phone, :shipping_address)'
            );
            $orderStatement->execute([
                'user_id' => $userId, 'order_code' => $orderCode, 'status' => 'pending',
                'subtotal' => $subtotal, 'shipping_fee' => 0, 'discount_amount' => $discount, 'voucher_code' => $voucherCode, 'total' => $total,
                'shipping_name' => $name, 'shipping_email' => $email, 'shipping_phone' => $phone, 'shipping_address' => $address,
            ]);
            $orderId = (int)$databaseConnection->lastInsertId();
            $databaseConnection->prepare(
                'INSERT INTO payments (order_id, method, status, amount, provider) VALUES (:order_id, :method, :status, :amount, :provider)'
            )->execute([
                'order_id' => $orderId,
                'method' => $paymentMethod,
                'status' => 'pending',
                'amount' => $total,
                'provider' => $paymentMethod === 'bank_transfer' ? 'payos' : null,
            ]);
            $databaseConnection->prepare(
                'INSERT INTO order_status_history (order_id, from_status, to_status, note) VALUES (:order_id, NULL, :status, :note)'
            )->execute(['order_id' => $orderId, 'status' => 'pending', 'note' => 'Đơn hàng được tạo, chờ xác nhận.']);
            $itemStatement = $databaseConnection->prepare(
                'INSERT INTO order_items (order_id, book_id, book_title, unit_price, quantity, line_total) '
                . 'VALUES (:order_id, :book_id, :book_title, :unit_price, :quantity, :line_total)'
            );
            $stockStatement = $databaseConnection->prepare('UPDATE books SET stock_quantity = stock_quantity - :quantity WHERE id = :id');
            foreach ($lockedItems as $item) {
                $book = $item['book'];
                $itemStatement->execute([
                    'order_id' => $orderId, 'book_id' => $book['id'], 'book_title' => $book['title'],
                    'unit_price' => $book['price'], 'quantity' => $item['quantity'], 'line_total' => $item['line_total'],
                ]);
                $stockStatement->execute(['quantity' => $item['quantity'], 'id' => $book['id']]);
            }
            $databaseConnection->commit();
            return $orderCode;
        } catch (\Throwable $exception) {
            if ($databaseConnection->inTransaction()) $databaseConnection->rollBack();
            throw $exception;
        }
    }

    public function markPayOSLink(int $orderId, string $paymentLinkId, array $response): void
    {
        $statement = $this->database->connection()->prepare(
            "UPDATE payments SET status = 'processing', provider = 'payos', transaction_code = :transaction_code,
                    provider_response = :provider_response WHERE order_id = :order_id AND method = 'bank_transfer'"
        );
        $statement->execute([
            'order_id' => $orderId,
            'transaction_code' => $paymentLinkId,
            'provider_response' => json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function markPayOSWebhook(int $orderId, int $amount, string $status, array $payload): void
    {
        $mappedStatus = 'failed';
        $status = strtoupper($status);
        if ($status === 'PAID') {
            $mappedStatus = 'paid';
        } elseif ($status === 'CANCELLED') {
            $mappedStatus = 'cancelled';
        } elseif ($status === 'PROCESSING') {
            $mappedStatus = 'processing';
        }
        $databaseConnection = $this->database->connection();
        $databaseConnection->beginTransaction();
        $changedOrderStatus = null;
        try {
            $orderStatement = $databaseConnection->prepare('SELECT user_id, status FROM orders WHERE id = :order_id FOR UPDATE');
            $orderStatement->execute(['order_id' => $orderId]);
            $currentOrder = $orderStatement->fetch();
            if (!$currentOrder) {
                throw new RuntimeException('Không tìm thấy đơn hàng.');
            }
            $payment = $databaseConnection->prepare('SELECT amount FROM payments WHERE order_id = :order_id AND method = \'bank_transfer\' FOR UPDATE');
            $payment->execute(['order_id' => $orderId]);
            $existing = $payment->fetch();
            if (!$existing || (int)$existing['amount'] !== $amount) {
                throw new RuntimeException('Số tiền thanh toán không khớp.');
            }
            $update = $databaseConnection->prepare('UPDATE payments SET status = :status, provider = \'payos\', transaction_code = COALESCE(:transaction_code, transaction_code), provider_response = :provider_response, paid_at = CASE WHEN :paid = 1 THEN COALESCE(paid_at, NOW()) ELSE paid_at END WHERE order_id = :order_id');
            $update->execute([
                'status' => $mappedStatus,
                'transaction_code' => $payload['data']['reference'] ?? ($payload['data']['paymentLinkId'] ?? null),
                'provider_response' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'paid' => $mappedStatus === 'paid' ? 1 : 0,
                'order_id' => $orderId,
            ]);
            if ($mappedStatus === 'paid') {
                if ($currentOrder['status'] === 'pending') $changedOrderStatus = 'confirmed';
            } elseif ($mappedStatus === 'cancelled') {
                if ($currentOrder['status'] === 'pending') $changedOrderStatus = 'cancelled';
            }
            if ($changedOrderStatus !== null) {
                $databaseConnection->prepare('UPDATE orders SET status = :status WHERE id = :id')->execute(['status' => $changedOrderStatus, 'id' => $orderId]);
                $databaseConnection->prepare('INSERT INTO order_status_history (order_id, from_status, to_status, changed_by, note) VALUES (:order_id, :from_status, :to_status, NULL, :note)')->execute(['order_id' => $orderId, 'from_status' => $currentOrder['status'], 'to_status' => $changedOrderStatus, 'note' => 'PayOS cập nhật thanh toán.']);
            }
            $databaseConnection->commit();
            if ($changedOrderStatus !== null) $this->sendStatusEmail($orderId, $changedOrderStatus, 'PayOS cập nhật thanh toán.');
        } catch (\Throwable $exception) {
            if ($databaseConnection->inTransaction()) $databaseConnection->rollBack();
            throw $exception;
        }
    }

    public function forUser(int $userId): array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll();
    }

    public function forUserWithItems(int $userId): array
    {
        $orders = $this->forUser($userId);
        $itemsModel = new OrderItem($this->database);
        foreach ($orders as &$order) {
            $order['items'] = $itemsModel->forUserOrder((int)$order['id'], $userId);
        }
        unset($order);
        return $orders;
    }

    public function detailForUser(int $orderId, int $userId): ?array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT orders.*, COALESCE(payments.method, \'cod\') AS payment_method, COALESCE(payments.status, \'pending\') AS payment_status '
            . 'FROM orders LEFT JOIN payments ON payments.order_id = orders.id '
            . 'WHERE orders.id = :id AND orders.user_id = :user_id LIMIT 1'
        );
        $statement->execute(['id' => $orderId, 'user_id' => $userId]);
        $order = $statement->fetch();
        if (!$order) {
            return null;
        }
        return $this->addDetailData($order);
    }

    public function detailForAdmin(int $orderId): ?array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT orders.*, users.name AS customer_name, users.email AS customer_email, '
            . 'COALESCE(payments.method, \'cod\') AS payment_method, COALESCE(payments.status, \'pending\') AS payment_status, '
            . 'payments.transaction_code, payments.paid_at '
            . 'FROM orders INNER JOIN users ON users.id = orders.user_id '
            . 'LEFT JOIN payments ON payments.order_id = orders.id WHERE orders.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $orderId]);
        $order = $statement->fetch();
        if (!$order) {
            return null;
        }
        return $this->addDetailData($order);
    }

    private function addDetailData(array $order): array
    {
        $order['items'] = (new OrderItem($this->database))->forOrder((int)$order['id']);
        $statement = $this->database->connection()->prepare(
            'SELECT order_status_history.*, users.name AS changed_by_name FROM order_status_history '
            . 'LEFT JOIN users ON users.id = order_status_history.changed_by '
            . 'WHERE order_status_history.order_id = :order_id ORDER BY order_status_history.created_at DESC, order_status_history.id DESC'
        );
        $statement->execute(['order_id' => (int)$order['id']]);
        $order['history'] = $statement->fetchAll();
        return $order;
    }

    public function cancelForUser(int $orderId, int $userId): void
    {
        $statement = $this->database->connection()->prepare('SELECT status FROM orders WHERE id = :id AND user_id = :user_id LIMIT 1');
        $statement->execute(['id' => $orderId, 'user_id' => $userId]);
        $order = $statement->fetch();
        if ($order && $order['status'] === 'pending') $this->changeStatus($orderId, 'cancelled', $userId, 'Thanh toán PayOS không hoàn tất.', 'admin');
    }

    public function allForAdmin(): array
    {
        return $this->database->connection()->query(
            'SELECT orders.*, users.name AS customer_name, users.email AS customer_email,
                    COALESCE(payments.method, \'cod\') AS payment_method,
                    COALESCE(payments.status, \'pending\') AS payment_status
             FROM orders
             INNER JOIN users ON users.id = orders.user_id
             LEFT JOIN payments ON payments.order_id = orders.id
             ORDER BY orders.created_at DESC'
        )->fetchAll();
    }

    public function paymentsForAdmin(?string $method = null, ?string $status = null): array
    {
        $sql = 'SELECT payments.*, orders.order_code, orders.status AS order_status,
                       orders.created_at AS order_created_at, users.name AS customer_name,
                       users.email AS customer_email
                FROM payments
                INNER JOIN orders ON orders.id = payments.order_id
                INNER JOIN users ON users.id = orders.user_id
                WHERE 1 = 1';
        $params = [];
        if ($method !== null && in_array($method, ['cod', 'bank_transfer', 'momo', 'vnpay', 'zalopay'], true)) {
            $sql .= ' AND payments.method = :method';
            $params['method'] = $method;
        }
        if ($status !== null && in_array($status, ['pending', 'processing', 'paid', 'failed', 'cancelled', 'refunded'], true)) {
            $sql .= ' AND payments.status = :payment_status';
            $params['payment_status'] = $status;
        }
        $sql .= ' ORDER BY payments.created_at DESC, payments.id DESC';
        $statement = $this->database->connection()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public function businessReports(): array
    {
        $databaseConnection = $this->database->connection();
        $summary = $databaseConnection->query(
            "SELECT
                COALESCE(SUM(total), 0) AS revenue,
                COUNT(*) AS orders,
                COALESCE(SUM(discount_amount), 0) AS discounts
             FROM orders
             WHERE status = 'completed'"
        )->fetch() ?: [];
        $payments = $databaseConnection->query(
            "SELECT COALESCE(p.method, 'cod') AS payment_method,
                    COALESCE(p.status, 'pending') AS payment_status,
                    COUNT(*) AS transactions, COALESCE(SUM(o.total), 0) AS amount
             FROM orders o
             LEFT JOIN payments p ON p.order_id = o.id
             GROUP BY COALESCE(p.method, 'cod'), COALESCE(p.status, 'pending')
             ORDER BY transactions DESC"
        )->fetchAll();
        $daily = $databaseConnection->query(
            "SELECT DATE(created_at) AS sale_date, COUNT(*) AS order_count,
                    COALESCE(SUM(subtotal), 0) AS gross_revenue,
                    COALESCE(SUM(discount_amount), 0) AS total_discount,
                    COALESCE(SUM(total), 0) AS net_revenue
             FROM orders
             WHERE status = 'completed'
             GROUP BY DATE(created_at) ORDER BY sale_date DESC LIMIT 30"
        )->fetchAll();
        $books = $databaseConnection->query(
            "SELECT oi.book_id, oi.book_title, b.author, c.name AS category_name,
                    SUM(oi.quantity) AS units_sold, SUM(oi.line_total) AS revenue
             FROM order_items oi
             INNER JOIN orders o ON o.id = oi.order_id
             INNER JOIN books b ON b.id = oi.book_id
             INNER JOIN categories c ON c.id = b.category_id
             WHERE o.status = 'completed'
             GROUP BY oi.book_id, oi.book_title, b.author, c.name
             ORDER BY revenue DESC, units_sold DESC LIMIT 10"
        )->fetchAll();
        $customers = $databaseConnection->query(
            "SELECT u.id AS customer_id, u.name, u.email,
                    ct.code AS customer_type, ct.name AS customer_tier,
                    COUNT(DISTINCT CASE WHEN o.status = 'completed' THEN o.id END) AS order_count,
                    COALESCE(SUM(CASE WHEN o.status = 'completed' THEN o.total ELSE 0 END), 0) AS total_spent,
                    MAX(o.created_at) AS last_order_at
             FROM users u
             LEFT JOIN customer_tiers ct ON ct.id = u.customer_tier_id
             LEFT JOIN orders o ON o.user_id = u.id
             WHERE u.role = 'customer'
             GROUP BY u.id, u.name, u.email, ct.code, ct.name
             ORDER BY total_spent DESC, order_count DESC"
        )->fetchAll();
        $history = $databaseConnection->query(
            'SELECT order_status_history.*, orders.order_code, users.name AS changed_by_name
             FROM order_status_history
             INNER JOIN orders ON orders.id = order_status_history.order_id
             LEFT JOIN users ON users.id = order_status_history.changed_by
             ORDER BY order_status_history.created_at DESC LIMIT 20'
        )->fetchAll();

        return [
            'revenue' => (int)($summary['revenue'] ?? 0),
            'orders' => (int)($summary['orders'] ?? 0),
            'discounts' => (int)($summary['discounts'] ?? 0),
            'payments' => $payments,
            'daily' => $daily,
            'books' => $books,
            'customers' => $customers,
            'history' => $history,
        ];
    }

    public function countAll(): int
    {
        return (int)$this->database->connection()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    }

    public function dashboardMetrics(): array
    {
        $databaseConnection = $this->database->connection();
        $summary = $databaseConnection->query("SELECT COALESCE(SUM(total), 0) AS revenue, COUNT(*) AS orders FROM orders WHERE status = 'completed'")->fetch() ?: [];
        $daily = $databaseConnection->query("SELECT DATE(created_at) AS period, SUM(total) AS revenue, COUNT(*) AS orders FROM orders WHERE status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) GROUP BY DATE(created_at) ORDER BY period DESC")->fetchAll();
        $monthly = $databaseConnection->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS period, SUM(total) AS revenue, COUNT(*) AS orders FROM orders WHERE status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY period DESC")->fetchAll();
        $topBooks = $databaseConnection->query("SELECT order_items.book_title, SUM(order_items.quantity) AS quantity, SUM(order_items.line_total) AS revenue FROM order_items INNER JOIN orders ON orders.id = order_items.order_id WHERE orders.status = 'completed' GROUP BY order_items.book_id, order_items.book_title ORDER BY quantity DESC, revenue DESC LIMIT 10")->fetchAll();
        $recentOrders = $databaseConnection->query("SELECT orders.order_code, orders.total, orders.status, orders.created_at, users.name AS customer_name FROM orders INNER JOIN users ON users.id = orders.user_id ORDER BY orders.created_at DESC LIMIT 8")->fetchAll();
        return ['revenue'=>(int)($summary['revenue'] ?? 0), 'orders'=>(int)($summary['orders'] ?? 0), 'daily'=>$daily, 'monthly'=>$monthly, 'topBooks'=>$topBooks, 'recentOrders'=>$recentOrders];
    }

    public function updateStatus(int $id, string $status, ?int $changedBy = null, string $note = ''): void
    {
        $this->changeStatus($id, $status, $changedBy, $note, 'admin');
    }

    public function cancelByCustomer(int $id, int $userId, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 250) {
            throw new RuntimeException('Lý do hủy đơn phải từ 1 đến 250 ký tự.');
        }
        $order = $this->detailForUser($id, $userId);
        if (!$order) {
            throw new RuntimeException('Không tìm thấy đơn hàng.');
        }
        if ($order['status'] === 'pending') {
            $this->changeStatus($id, 'cancelled', $userId, $reason, 'customer');
            $this->saveCancelReason($id, $reason, false);
            return;
        }
        if ($order['status'] !== 'confirmed') {
            throw new RuntimeException('Chỉ được yêu cầu hủy khi đơn chưa giao hàng.');
        }
        $this->changeStatus($id, 'cancel_requested', $userId, $reason, 'customer');
        $this->saveCancelReason($id, $reason, true);
    }

    public function reviewCancel(int $id, bool $approve, int $adminId, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 250) {
            throw new RuntimeException('Lý do xử lý yêu cầu hủy phải từ 1 đến 250 ký tự.');
        }
        $order = $this->findById($id);
        if (!$order || $order['status'] !== 'cancel_requested') {
            throw new RuntimeException('Yêu cầu hủy không còn cần xử lý.');
        }
        $status = $approve ? 'cancelled' : 'confirmed';
        $this->changeStatus($id, $status, $adminId, $reason, 'admin');
        $this->saveCancelReason($id, $reason, false);
    }

    public function confirmReceived(int $id, int $userId): void
    {
        $order = $this->detailForUser($id, $userId);
        if (!$order || $order['status'] !== 'delivered') {
            throw new RuntimeException('Đơn hàng chưa ở trạng thái chờ xác nhận nhận hàng.');
        }
        $this->changeStatus($id, 'completed', $userId, 'Khách hàng xác nhận đã nhận được hàng.', 'customer');
    }

    public function requestReturn(int $id, int $userId, string $reason, ?string $image): void
    {
        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 250) {
            throw new RuntimeException('Lý do đổi trả phải từ 1 đến 250 ký tự.');
        }

        if ($image === null || $image === '') {
            throw new RuntimeException('Vui lòng gửi hình ảnh sản phẩm cần đổi trả.');
        }
        $order = $this->detailForUser($id, $userId);
        if (!$order || $order['status'] !== 'delivered') {
            throw new RuntimeException('Chỉ được yêu cầu đổi trả khi đơn đã giao thành công.');
        }
        $this->changeStatus($id, 'return_requested', $userId, $reason, 'customer');
        $statement = $this->database->connection()->prepare('UPDATE orders SET return_reason = :reason, return_image = :image, return_requested_at = NOW() WHERE id = :id');
        $statement->execute(['reason' => $reason, 'image' => $image, 'id' => $id]);
    }

    public function reviewReturn(int $id, bool $approve, int $adminId, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 250) {
            throw new RuntimeException('Lý do xử lý đổi trả phải từ 1 đến 250 ký tự.');
        }
        $order = $this->findById($id);
        if (!$order || $order['status'] !== 'return_requested') {
            throw new RuntimeException('Yêu cầu đổi trả không còn cần xử lý.');
        }
        $this->changeStatus($id, $approve ? 'return_shipping' : 'return_rejected', $adminId, $reason, 'admin');
        $statement = $this->database->connection()->prepare('UPDATE orders SET return_review_reason = :reason, return_reviewed_at = NOW() WHERE id = :id');
        $statement->execute(['reason' => $reason, 'id' => $id]);
    }

    public function confirmReturned(int $id, int $adminId, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 250) {
            throw new RuntimeException('Ghi chú nhận hàng trả phải từ 1 đến 250 ký tự.');
        }
        $order = $this->findById($id);
        if (!$order || $order['status'] !== 'return_shipping') {
            throw new RuntimeException('Đơn hàng chưa ở trạng thái chờ nhận hàng trả.');
        }
        $this->changeStatus($id, 'returned', $adminId, $reason, 'admin');
    }

    private function changeStatus(int $id, string $status, ?int $changedBy, string $note, string $actor): void
    {
        $allowed = ['pending', 'confirmed', 'shipping', 'delivered', 'completed', 'cancel_requested', 'return_requested', 'return_shipping', 'returned', 'return_rejected', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            throw new RuntimeException('Trạng thái đơn hàng không hợp lệ.');
        }
        $databaseConnection = $this->database->connection();
        $databaseConnection->beginTransaction();
        try {
            $statement = $databaseConnection->prepare('SELECT orders.*, COALESCE(payments.method, \'cod\') AS payment_method, COALESCE(payments.status, \'pending\') AS payment_status FROM orders LEFT JOIN payments ON payments.order_id = orders.id WHERE orders.id = :id FOR UPDATE');
            $statement->execute(['id' => $id]);
            $current = $statement->fetch();
            if (!$current) {
                throw new RuntimeException('Không tìm thấy đơn hàng.');
            }

            if ($current['status'] === $status) {
                $databaseConnection->commit();
                return;
            }

            if ($status === 'cancelled' && trim($note) === '') {
                throw new RuntimeException('Hủy đơn hàng bắt buộc phải có lý do.');
            }

            if (!$this->canChangeStatus((string)$current['status'], $status, $actor)) {
                throw new RuntimeException('Đơn hàng chỉ được chuyển sang bước kế tiếp.');
            }

            $isUnpaidBankTransfer = $status === 'completed'
                && $current['payment_method'] === 'bank_transfer'
                && $current['payment_status'] !== 'paid';
            if ($isUnpaidBankTransfer) {
                throw new RuntimeException('Chưa thể hoàn tất đơn PayOS khi giao dịch chưa được xác nhận.');
            }

            if ($status === 'cancelled' && $current['payment_status'] === 'paid') {
                throw new RuntimeException('Đơn đã thanh toán, cần xử lý hoàn tiền trước khi hủy.');
            }

            $update = $databaseConnection->prepare('UPDATE orders SET status = :status WHERE id = :id');
            $update->execute(['status' => $status, 'id' => $id]);
            if ($status === 'cancelled' || $status === 'cancel_requested') {
                $databaseConnection->prepare('UPDATE orders SET cancel_reason = :reason, cancel_requested_at = CASE WHEN :requested = 1 THEN NOW() ELSE cancel_requested_at END WHERE id = :id')->execute(['reason' => $note, 'requested' => $status === 'cancel_requested' ? 1 : 0, 'id' => $id]);
            }
            $history = $databaseConnection->prepare('INSERT INTO order_status_history (order_id, from_status, to_status, changed_by, note) VALUES (:order_id, :from_status, :to_status, :changed_by, :note)');
            $historyNote = $note !== '' ? $note : 'Cập nhật trạng thái đơn hàng.';
            if (function_exists('mb_substr')) $historyNote = mb_substr($historyNote, 0, 250);
            else $historyNote = substr($historyNote, 0, 250);
            $history->execute(['order_id' => $id, 'from_status' => $current['status'], 'to_status' => $status, 'changed_by' => $changedBy, 'note' => $historyNote]);

            if ($status === 'completed') {
                $payment = $databaseConnection->prepare("INSERT INTO payments (order_id, method, status, amount, paid_at) VALUES (:order_id, 'cod', 'paid', :amount, NOW()) ON DUPLICATE KEY UPDATE status = 'paid', paid_at = COALESCE(paid_at, NOW()), amount = VALUES(amount)");
                $payment->execute(['order_id' => $id, 'amount' => (int)$current['total']]);
            }
            if ($status === 'returned') {
                $databaseConnection->prepare("UPDATE payments SET status = 'refunded' WHERE order_id = :order_id")->execute(['order_id' => $id]);
                $this->restoreReturnedStock($databaseConnection, $id);
            }
            if ($status === 'cancelled') {
                $this->restoreCancelledOrder($databaseConnection, $current);
            }

            $shouldUpdateTier = $status === 'completed' || $status === 'returned' || $status === 'cancelled';
            if ($shouldUpdateTier) {
                $this->updateCustomerTier($databaseConnection, (int)$current['user_id']);
            }

            $databaseConnection->commit();
            if ($status === 'completed') {
                (new MemberPoint($this->database))->earnForOrder((int)$current['user_id'], $id, (int)$current['total']);
            }
            $this->sendStatusEmail($id, $status, $note);
        } catch (\Throwable $exception) {
            if ($databaseConnection->inTransaction()) $databaseConnection->rollBack();
            throw $exception;
        }
    }

    private function canChangeStatus(string $from, string $to, string $actor): bool
    {
        $adminSteps = ['pending' => ['confirmed', 'cancelled'], 'confirmed' => ['shipping', 'cancelled'], 'shipping' => ['delivered'], 'cancel_requested' => ['confirmed', 'cancelled'], 'return_requested' => ['return_shipping', 'return_rejected'], 'return_shipping' => ['returned']];
        $customerSteps = ['pending' => ['cancelled'], 'confirmed' => ['cancel_requested'], 'delivered' => ['completed', 'return_requested']];
        $steps = $actor === 'customer' ? $customerSteps : $adminSteps;
        return in_array($to, $steps[$from] ?? [], true);
    }

    private function restoreCancelledOrder($databaseConnection, array $order): void
    {
        $databaseConnection->prepare("UPDATE payments SET status = 'cancelled' WHERE order_id = :order_id AND status <> 'paid'")->execute(['order_id' => (int)$order['id']]);
        $items = $databaseConnection->prepare('SELECT book_id, quantity FROM order_items WHERE order_id = :order_id');
        $items->execute(['order_id' => (int)$order['id']]);
        $restore = $databaseConnection->prepare('UPDATE books SET stock_quantity = stock_quantity + :quantity WHERE id = :book_id');
        foreach ($items->fetchAll() as $item) {
            $restore->execute(array(
                'quantity' => (int)$item['quantity'],
                'book_id' => (int)$item['book_id'],
            ));
        }

        if (!empty($order['voucher_code'])) {
            $statement = $databaseConnection->prepare('UPDATE vouchers SET used_count = GREATEST(0, used_count - 1) WHERE code = :code');
            $statement->execute(['code' => $order['voucher_code']]);
        }
    }

    private function restoreReturnedStock($databaseConnection, int $orderId): void
    {
        $items = $databaseConnection->prepare('SELECT book_id, quantity FROM order_items WHERE order_id = :order_id');
        $items->execute(['order_id' => $orderId]);
        $restore = $databaseConnection->prepare('UPDATE books SET stock_quantity = stock_quantity + :quantity WHERE id = :book_id');
        foreach ($items->fetchAll() as $item) {
            $restore->execute(array(
                'quantity' => (int)$item['quantity'],
                'book_id' => (int)$item['book_id'],
            ));
        }
    }

    private function updateCustomerTier($databaseConnection, int $userId): void
    {
        $tier = $databaseConnection->prepare("SELECT id FROM customer_tiers WHERE min_spending <= (SELECT COALESCE(SUM(total), 0) FROM orders WHERE user_id = :user_id AND status = 'completed') ORDER BY min_spending DESC LIMIT 1");
        $tier->execute(['user_id' => $userId]);
        $tierId = $tier->fetchColumn();
        if ($tierId !== false) $databaseConnection->prepare('UPDATE users SET customer_tier_id = :tier_id WHERE id = :user_id')->execute(['tier_id' => (int)$tierId, 'user_id' => $userId]);
    }

    private function saveCancelReason(int $id, string $reason, bool $requested): void
    {
        $statement = $this->database->connection()->prepare('UPDATE orders SET cancel_reason = :reason, cancel_requested_at = CASE WHEN :requested = 1 THEN NOW() ELSE cancel_requested_at END WHERE id = :id');
        $statement->execute(['reason' => $reason, 'requested' => $requested ? 1 : 0, 'id' => $id]);
    }

    public function notifyNewOrder(string $orderCode): void
    {
        $order = $this->findByCode($orderCode);
        if (!$order) {
            return;
        }
        $adminEmail = (new SystemSetting($this->database))->get('SHOP_EMAIL');
        if (!$adminEmail || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $this->sendEmail($adminEmail, 'Có đơn hàng mới ' . $orderCode, 'Có đơn hàng mới ' . $orderCode . '. Vui lòng kiểm tra trong trang quản trị.', EmailTemplate::newOrder('Quản trị viên', $orderCode, $this->baseUrl() . '/admin/orders/' . (int)$order['id']));
    }

    private function sendStatusEmail(int $id, string $status, string $reason): void
    {
        $order = $this->findById($id);
        if (!$order || !filter_var($order['shipping_email'], FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $this->sendEmail((string)$order['shipping_email'], 'Cập nhật đơn hàng ' . $order['order_code'], 'Đơn hàng ' . $order['order_code'] . ' đã chuyển sang trạng thái mới.', EmailTemplate::orderStatus((string)$order['shipping_name'], (string)$order['order_code'], $status, $reason, $this->baseUrl() . '/orders/' . (int)$order['id']));
    }

    private function sendEmail(string $to, string $subject, string $body, string $html): void
    {
        try {
            $mailer = new SmtpMailer((new SystemSetting($this->database))->mailConfig());
            $mailer->send($to, $subject, $body, $html);
        } catch (\Throwable $exception) {
            error_log('Order email failed: ' . $exception->getMessage());
        }
    }

    private function baseUrl(): string
    {
        return rtrim((string)(getenv('APP_URL') ?: 'http://localhost'), '/');
    }
}
