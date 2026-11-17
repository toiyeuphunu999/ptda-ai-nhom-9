<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use RuntimeException;

final class Review
{
    public function __construct(private Database $database) {}

    public function forBook(int $bookId): array
    {
        $statement = $this->database->connection()->prepare('SELECT reviews.*, users.name AS user_name FROM reviews INNER JOIN users ON users.id = reviews.user_id WHERE reviews.book_id = :book_id AND reviews.status = "published" ORDER BY reviews.created_at DESC');
        $statement->execute(['book_id' => $bookId]);
        return $statement->fetchAll();
    }

    public function summary(int $bookId): array
    {
        $statement = $this->database->connection()->prepare('SELECT ROUND(AVG(rating), 1) AS average, COUNT(*) AS total FROM reviews WHERE book_id = :book_id AND status = "published"');
        $statement->execute(['book_id' => $bookId]);
        $row = $statement->fetch() ?: [];
        return ['average' => (float)($row['average'] ?? 0), 'total' => (int)($row['total'] ?? 0)];
    }

    public function canReview(int $userId, int $bookId): bool
    {
        $statement = $this->database->connection()->prepare('SELECT COUNT(*) FROM order_items INNER JOIN orders ON orders.id = order_items.order_id WHERE orders.user_id = :user_id AND order_items.book_id = :book_id AND orders.status = "completed"');
        $statement->execute(['user_id'=>$userId,'book_id'=>$bookId]);
        return (int)$statement->fetchColumn() > 0;
    }

    public function hasReview(int $userId, int $bookId): bool
    {
        $statement = $this->database->connection()->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = :user_id AND book_id = :book_id');
        $statement->execute(['user_id'=>$userId,'book_id'=>$bookId]);
        return (int)$statement->fetchColumn() > 0;
    }

    public function save(int $userId, int $bookId, int $rating, string $comment): int
    {
        if ($rating < 1 || $rating > 5 || trim($comment) === '' || mb_strlen($comment) > 2000) throw new RuntimeException('Đánh giá cần từ 1 đến 5 sao và nội dung tối đa 2.000 ký tự.');
        $statement = $this->database->connection()->prepare('INSERT INTO reviews (user_id,book_id,rating,comment) VALUES (:user_id,:book_id,:rating,:comment)');
        $statement->execute(['user_id'=>$userId,'book_id'=>$bookId,'rating'=>$rating,'comment'=>trim($comment)]);
        return (int)$this->database->connection()->lastInsertId();
    }
}
