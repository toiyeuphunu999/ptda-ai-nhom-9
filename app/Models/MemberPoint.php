<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class MemberPoint
{
    public const ORDER_POINT_RATE = 10000;
    public const REVIEW_POINTS = 50;
    public const REFERRER_POINTS = 200;
    public const REFERRED_POINTS = 100;

    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function balance(int $userId): int
    {
        $statement = $this->database->connection()->prepare(
            'SELECT COALESCE(SUM(points), 0) FROM member_point_transactions WHERE user_id = :user_id'
        );
        $statement->execute(['user_id' => $userId]);
        return (int)$statement->fetchColumn();
    }

    public function history(int $userId): array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT points, transaction_type, description, created_at
             FROM member_point_transactions
             WHERE user_id = :user_id
             ORDER BY created_at DESC, id DESC LIMIT 30'
        );
        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll();
    }

    public function earnForOrder(int $userId, int $orderId, int $total): void
    {
        $points = intdiv(max(0, $total), self::ORDER_POINT_RATE);
        if ($points > 0) {
            $this->addOnce($userId, $points, 'order', $orderId, 'Tích điểm từ đơn hàng hoàn tất.');
        }
    }

    public function earnForReview(int $userId, int $reviewId): void
    {
        $this->addOnce($userId, self::REVIEW_POINTS, 'review', $reviewId, 'Tích điểm khi đánh giá sách.');
    }

    public function earnForReferral(int $newUserId): void
    {
        $statement = $this->database->connection()->prepare('SELECT referred_by FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $newUserId]);
        $referrerId = $statement->fetchColumn();
        if (!$referrerId) {
            return;
        }

        $this->addOnce((int)$referrerId, self::REFERRER_POINTS, 'referral', $newUserId, 'Thưởng giới thiệu thành viên mới.');
        $this->addOnce($newUserId, self::REFERRED_POINTS, 'referral', $newUserId, 'Thưởng đăng ký qua mã giới thiệu.');
    }

    private function addOnce(int $userId, int $points, string $sourceType, int $sourceId, string $description): void
    {
        $statement = $this->database->connection()->prepare(
            'INSERT IGNORE INTO member_point_transactions
             (user_id, points, transaction_type, source_type, source_id, description)
             VALUES (:user_id, :points, :transaction_type, :source_type, :source_id, :description)'
        );
        $statement->execute([
            'user_id' => $userId,
            'points' => $points,
            'transaction_type' => 'earned',
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'description' => $description,
        ]);
    }
}
