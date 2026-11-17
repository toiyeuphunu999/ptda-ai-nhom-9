<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use RuntimeException;
use App\Models\Voucher;

final class Order
{
    public function __construct(private Database $database)
    {
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

    public function createFromCart(int $userId, array $items, string $name, string $phone, string $address, ?int $voucherId = null, string $paymentMethod = 'cod'): string
    {
        if (!in_array($paymentMethod, ['cod', 'bank_transfer'], true)) {
            throw new RuntimeException('Phương thức thanh toán không hợp lệ.');
        }
        $pdo = $this->database->connection();
        $pdo->beginTransaction();
        try {
            $subtotal = 0;
            $lockedItems = [];
            foreach ($items as $item) {
                $statement = $pdo->prepare('SELECT * FROM books WHERE id = :id AND status = :status FOR UPDATE');
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
                $voucherStatement = $pdo->prepare('SELECT * FROM vouchers WHERE id = :id FOR UPDATE');
                $voucherStatement->execute(['id' => $voucherId]);
                $voucher = $voucherStatement->fetch();
                if (!$voucher) throw new RuntimeException('Mã giảm giá không tồn tại.');
                $discount = Voucher::discountFor($voucher, $subtotal);
                $voucherCode = $voucher['code'];
                $pdo->prepare('UPDATE vouchers SET used_count = used_count + 1 WHERE id = :id')->execute(['id' => $voucherId]);
            }
            $total = max(0, $subtotal - $discount);

            $orderCode = 'MB' . date('ymdHis') . strtoupper(bin2hex(random_bytes(3)));
            $orderStatement = $pdo->prepare(
                'INSERT INTO orders (user_id, order_code, status, subtotal, shipping_fee, discount_amount, voucher_code, total, shipping_name, shipping_phone, shipping_address) '
                . 'VALUES (:user_id, :order_code, :status, :subtotal, :shipping_fee, :discount_amount, :voucher_code, :total, :shipping_name, :shipping_phone, :shipping_address)'
            );
            $orderStatement->execute([
                'user_id' => $userId, 'order_code' => $orderCode, 'status' => 'pending',
                'subtotal' => $subtotal, 'shipping_fee' => 0, 'discount_amount' => $discount, 'voucher_code' => $voucherCode, 'total' => $total,
                'shipping_name' => $name, 'shipping_phone' => $phone, 'shipping_address' => $address,
            ]);
            $orderId = (int)$pdo->lastInsertId();
            $pdo->prepare(
                'INSERT INTO payments (order_id, method, status, amount, provider) VALUES (:order_id, :method, :status, :amount, :provider)'
            )->execute([
                'order_id' => $orderId,
                'method' => $paymentMethod,
                'status' => 'pending',
                'amount' => $total,
                'provider' => $paymentMethod === 'bank_transfer' ? 'payos' : null,
            ]);
            $pdo->prepare(
                'INSERT INTO order_status_history (order_id, from_status, to_status, note) VALUES (:order_id, NULL, :status, :note)'
            )->execute(['order_id' => $orderId, 'status' => 'pending', 'note' => 'Đơn hàng được tạo, chờ xác nhận.']);
            $itemStatement = $pdo->prepare(
                'INSERT INTO order_items (order_id, book_id, book_title, unit_price, quantity, line_total) '
                . 'VALUES (:order_id, :book_id, :book_title, :unit_price, :quantity, :line_total)'
            );
            $stockStatement = $pdo->prepare('UPDATE books SET stock_quantity = stock_quantity - :quantity WHERE id = :id');
            foreach ($lockedItems as $item) {
                $book = $item['book'];
                $itemStatement->execute([
                    'order_id' => $orderId, 'book_id' => $book['id'], 'book_title' => $book['title'],
                    'unit_price' => $book['price'], 'quantity' => $item['quantity'], 'line_total' => $item['line_total'],
                ]);
                $stockStatement->execute(['quantity' => $item['quantity'], 'id' => $book['id']]);
            }
            $pdo->commit();
            return $orderCode;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
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
        $mappedStatus = match (strtoupper($status)) {
            'PAID' => 'paid',
            'CANCELLED' => 'cancelled',
            'PROCESSING' => 'processing',
            default => 'failed',
        };
        $pdo = $this->database->connection();
        $pdo->beginTransaction();
        try {
            $payment = $pdo->prepare('SELECT amount FROM payments WHERE order_id = :order_id AND method = \'bank_transfer\' FOR UPDATE');
            $payment->execute(['order_id' => $orderId]);
            $existing = $payment->fetch();
            if (!$existing || (int)$existing['amount'] !== $amount) throw new RuntimeException('Số tiền thanh toán không khớp.');
            $update = $pdo->prepare('UPDATE payments SET status = :status, provider = \'payos\', transaction_code = COALESCE(:transaction_code, transaction_code), provider_response = :provider_response, paid_at = CASE WHEN :paid = 1 THEN COALESCE(paid_at, NOW()) ELSE paid_at END WHERE order_id = :order_id');
            $update->execute([
                'status' => $mappedStatus,
                'transaction_code' => $payload['data']['reference'] ?? ($payload['data']['paymentLinkId'] ?? null),
                'provider_response' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'paid' => $mappedStatus === 'paid' ? 1 : 0,
                'order_id' => $orderId,
            ]);
            if ($mappedStatus === 'paid') {
                $pdo->prepare("UPDATE orders SET status = CASE WHEN status = 'pending' THEN 'confirmed' ELSE status END WHERE id = :id AND status <> 'cancelled'")->execute(['id' => $orderId]);
            } elseif ($mappedStatus === 'cancelled') {
                $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = :id AND status = 'pending'")->execute(['id' => $orderId]);
            }
            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
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

    public function cancelForUser(int $orderId, int $userId): void
    {
        $statement = $this->database->connection()->prepare('SELECT status FROM orders WHERE id = :id AND user_id = :user_id LIMIT 1');
        $statement->execute(['id' => $orderId, 'user_id' => $userId]);
        $order = $statement->fetch();
        if ($order && $order['status'] === 'pending') $this->updateStatus($orderId, 'cancelled', $userId);
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
        $pdo = $this->database->connection();
        $summary = $pdo->query(
            "SELECT
                COALESCE(SUM(total), 0) AS revenue,
                COUNT(*) AS orders,
                COALESCE(SUM(discount_amount), 0) AS discounts
             FROM orders
             WHERE status IN ('confirmed', 'shipping', 'completed')"
        )->fetch() ?: [];
        $payments = $pdo->query(
            "SELECT COALESCE(p.method, 'cod') AS payment_method,
                    COALESCE(p.status, 'pending') AS payment_status,
                    COUNT(*) AS transactions, COALESCE(SUM(o.total), 0) AS amount
             FROM orders o
             LEFT JOIN payments p ON p.order_id = o.id
             GROUP BY COALESCE(p.method, 'cod'), COALESCE(p.status, 'pending')
             ORDER BY transactions DESC"
        )->fetchAll();
        $daily = $pdo->query(
            "SELECT DATE(created_at) AS sale_date, COUNT(*) AS order_count,
                    COALESCE(SUM(subtotal), 0) AS gross_revenue,
                    COALESCE(SUM(discount_amount), 0) AS total_discount,
                    COALESCE(SUM(total), 0) AS net_revenue
             FROM orders
             WHERE status IN ('confirmed', 'shipping', 'completed')
             GROUP BY DATE(created_at) ORDER BY sale_date DESC LIMIT 30"
        )->fetchAll();
        $books = $pdo->query(
            "SELECT oi.book_id, oi.book_title, b.author, c.name AS category_name,
                    SUM(oi.quantity) AS units_sold, SUM(oi.line_total) AS revenue
             FROM order_items oi
             INNER JOIN orders o ON o.id = oi.order_id
             INNER JOIN books b ON b.id = oi.book_id
             INNER JOIN categories c ON c.id = b.category_id
             WHERE o.status IN ('confirmed', 'shipping', 'completed')
             GROUP BY oi.book_id, oi.book_title, b.author, c.name
             ORDER BY revenue DESC, units_sold DESC LIMIT 10"
        )->fetchAll();
        $customers = $pdo->query(
            "SELECT u.id AS customer_id, u.name, u.email,
                    ct.code AS customer_type, ct.name AS customer_tier,
                    COUNT(DISTINCT CASE WHEN o.status IN ('confirmed', 'shipping', 'completed') THEN o.id END) AS order_count,
                    COALESCE(SUM(CASE WHEN o.status IN ('confirmed', 'shipping', 'completed') THEN o.total ELSE 0 END), 0) AS total_spent,
                    MAX(o.created_at) AS last_order_at
             FROM users u
             LEFT JOIN customer_tiers ct ON ct.id = u.customer_tier_id
             LEFT JOIN orders o ON o.user_id = u.id
             WHERE u.role = 'customer'
             GROUP BY u.id, u.name, u.email, ct.code, ct.name
             ORDER BY total_spent DESC, order_count DESC"
        )->fetchAll();
        $history = $pdo->query(
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
        $pdo = $this->database->connection();
        $summary = $pdo->query("SELECT COALESCE(SUM(total), 0) AS revenue, COUNT(*) AS orders FROM orders WHERE status IN ('confirmed', 'shipping', 'completed')")->fetch() ?: [];
        $daily = $pdo->query("SELECT DATE(created_at) AS period, SUM(total) AS revenue, COUNT(*) AS orders FROM orders WHERE status IN ('confirmed', 'shipping', 'completed') AND created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) GROUP BY DATE(created_at) ORDER BY period DESC")->fetchAll();
        $monthly = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS period, SUM(total) AS revenue, COUNT(*) AS orders FROM orders WHERE status IN ('confirmed', 'shipping', 'completed') AND created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY period DESC")->fetchAll();
        $topBooks = $pdo->query("SELECT order_items.book_title, SUM(order_items.quantity) AS quantity, SUM(order_items.line_total) AS revenue FROM order_items INNER JOIN orders ON orders.id = order_items.order_id WHERE orders.status IN ('confirmed', 'shipping', 'completed') GROUP BY order_items.book_id, order_items.book_title ORDER BY quantity DESC, revenue DESC LIMIT 10")->fetchAll();
        $recentOrders = $pdo->query("SELECT orders.order_code, orders.total, orders.status, orders.created_at, users.name AS customer_name FROM orders INNER JOIN users ON users.id = orders.user_id ORDER BY orders.created_at DESC LIMIT 8")->fetchAll();
        return ['revenue'=>(int)($summary['revenue'] ?? 0), 'orders'=>(int)($summary['orders'] ?? 0), 'daily'=>$daily, 'monthly'=>$monthly, 'topBooks'=>$topBooks, 'recentOrders'=>$recentOrders];
    }

    public function updateStatus(int $id, string $status, ?int $changedBy = null): void
    {
        if (!in_array($status, ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'], true)) {
            throw new RuntimeException('Trạng thái đơn hàng không hợp lệ.');
        }
        $pdo = $this->database->connection();
        $pdo->beginTransaction();
        try {
            $currentStatement = $pdo->prepare('SELECT orders.user_id, orders.status, orders.total, orders.voucher_code,
                                                       COALESCE(payments.method, \'cod\') AS payment_method,
                                                       COALESCE(payments.status, \'pending\') AS payment_status
                                                FROM orders
                                                LEFT JOIN payments ON payments.order_id = orders.id
                                                WHERE orders.id = :id FOR UPDATE');
            $currentStatement->execute(['id' => $id]);
            $current = $currentStatement->fetch();
            if (!$current) throw new RuntimeException('Không tìm thấy đơn hàng.');
            if ($current['status'] === $status) {
                $pdo->commit();
                return;
            }
            if (in_array($current['status'], ['cancelled', 'completed'], true)) {
                throw new RuntimeException('Đơn hàng đã kết thúc và không thể đổi sang trạng thái khác.');
            }
            if ($status === 'completed'
                && $current['payment_method'] === 'bank_transfer'
                && $current['payment_status'] !== 'paid') {
                throw new RuntimeException('Chưa thể hoàn tất đơn PayOS khi giao dịch chưa được xác nhận.');
            }
            if ($status === 'cancelled' && $current['payment_status'] === 'paid') {
                throw new RuntimeException('Đơn đã thanh toán không thể hủy trực tiếp; cần thực hiện quy trình hoàn tiền.');
            }

            $statement = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
            $statement->execute(['status' => $status, 'id' => $id]);
            $history = $pdo->prepare(
                'INSERT INTO order_status_history (order_id, from_status, to_status, changed_by, note) VALUES (:order_id, :from_status, :to_status, :changed_by, :note)'
            );
            $history->execute([
                'order_id' => $id,
                'from_status' => $current['status'],
                'to_status' => $status,
                'changed_by' => $changedBy,
                'note' => 'Admin cập nhật trạng thái đơn hàng.',
            ]);

            if ($status === 'completed') {
                $payment = $pdo->prepare(
                    "INSERT INTO payments (order_id, method, status, amount, paid_at)
                     VALUES (:order_id, 'cod', 'paid', :amount, NOW())
                     ON DUPLICATE KEY UPDATE status = 'paid', paid_at = COALESCE(paid_at, NOW()), amount = VALUES(amount)"
                );
                $payment->execute(['order_id' => $id, 'amount' => (int)$current['total']]);
            } elseif ($status === 'cancelled') {
                $pdo->prepare("UPDATE payments SET status = 'cancelled' WHERE order_id = :order_id AND status <> 'paid'")
                    ->execute(['order_id' => $id]);
                $items = $pdo->prepare('SELECT book_id, quantity FROM order_items WHERE order_id = :order_id');
                $items->execute(['order_id' => $id]);
                $restoreStock = $pdo->prepare('UPDATE books SET stock_quantity = stock_quantity + :quantity WHERE id = :book_id');
                foreach ($items->fetchAll() as $item) {
                    $restoreStock->execute(['quantity' => (int)$item['quantity'], 'book_id' => (int)$item['book_id']]);
                }
                if (!empty($current['voucher_code'])) {
                    $pdo->prepare('UPDATE vouchers SET used_count = GREATEST(0, used_count - 1) WHERE code = :code')
                        ->execute(['code' => $current['voucher_code']]);
                }
            }

            if (in_array($status, ['completed', 'cancelled'], true)) {
                $tierStatement = $pdo->prepare(
                    "SELECT id FROM customer_tiers
                     WHERE min_spending <= (
                         SELECT COALESCE(SUM(total), 0) FROM orders
                         WHERE user_id = :user_id AND status IN ('confirmed', 'shipping', 'completed')
                     )
                     ORDER BY min_spending DESC LIMIT 1"
                );
                $tierStatement->execute(['user_id' => (int)$current['user_id']]);
                $tierId = $tierStatement->fetchColumn();
                if ($tierId !== false) {
                    $pdo->prepare('UPDATE users SET customer_tier_id = :tier_id WHERE id = :user_id')
                        ->execute(['tier_id' => (int)$tierId, 'user_id' => (int)$current['user_id']]);
                }
            }
            $pdo->commit();
            if ($status === 'completed') {
                (new MemberPoint($this->database))->earnForOrder((int)$current['user_id'], $id, (int)$current['total']);
            }
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }
}
