<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Notification
{
    public function __construct(
        private Database $database
    ) {
    }

    // Tạo thông báo
    public function create(
        int $userId,
        ?int $orderId,
        string $title,
        string $message
    ): void {
        $sql = "INSERT INTO notifications
                (user_id, order_id, title, message)
                VALUES
                (:user_id, :order_id, :title, :message)";

        $stmt = $this->database->connection()->prepare($sql);

        $stmt->execute([
            'user_id' => $userId,
            'order_id' => $orderId,
            'title' => $title,
            'message' => $message
        ]);
    }

    // Lấy thông báo của một khách hàng
    public function forUser(int $userId): array
    {
        $sql = "SELECT *
                FROM notifications
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT 50";

        $stmt = $this->database->connection()->prepare($sql);

        $stmt->execute([
            'user_id' => $userId
        ]);

        return $stmt->fetchAll();
    }

    // Đếm thông báo chưa đọc
    public function unreadCount(int $userId): int
    {
        $sql = "SELECT COUNT(*)
                FROM notifications
                WHERE user_id = :user_id
                AND is_read = 0";

        $stmt = $this->database->connection()->prepare($sql);

        $stmt->execute([
            'user_id' => $userId
        ]);

        return (int)$stmt->fetchColumn();
    }

    // Đánh dấu thông báo đã đọc
    public function markRead(
        int $notificationId,
        int $userId
    ): void {
        $sql = "UPDATE notifications
                SET is_read = 1
                WHERE id = :id
                AND user_id = :user_id";

        $stmt = $this->database->connection()->prepare($sql);

        $stmt->execute([
            'id' => $notificationId,
            'user_id' => $userId
        ]);
    }

    // Đánh dấu tất cả đã đọc
    public function markAllRead(int $userId): void
    {
        $sql = "UPDATE notifications
                SET is_read = 1
                WHERE user_id = :user_id";

        $stmt = $this->database->connection()->prepare($sql);

        $stmt->execute([
            'user_id' => $userId
        ]);
    }
}