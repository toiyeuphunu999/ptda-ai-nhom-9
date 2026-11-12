<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class OrderItem
{
    public function __construct(private Database $database)
    {
    }

    public function forOrder(int $orderId): array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT * FROM order_items WHERE order_id = :order_id ORDER BY id'
        );
        $statement->execute(['order_id' => $orderId]);
        return $statement->fetchAll();
    }

    public function forUserOrder(int $orderId, int $userId): array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT order_items.*, books.slug AS book_slug, reviews.id AS review_id FROM order_items INNER JOIN orders ON orders.id = order_items.order_id '
            . 'INNER JOIN books ON books.id = order_items.book_id '
            . 'LEFT JOIN reviews ON reviews.book_id = order_items.book_id AND reviews.user_id = orders.user_id '
            . 'WHERE order_items.order_id = :order_id AND orders.user_id = :user_id ORDER BY order_items.id'
        );
        $statement->execute(['order_id' => $orderId, 'user_id' => $userId]);
        return $statement->fetchAll();
    }
}
