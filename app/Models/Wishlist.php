<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Wishlist
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function forUser(int $userId): array
    {
        $query = $this->database->connection()->prepare(
            "SELECT books.*,
                    categories.name AS category_name,
                    categories.slug AS category_slug
             FROM wishlists
             INNER JOIN books ON books.id = wishlists.book_id
             INNER JOIN categories ON categories.id = books.category_id
             WHERE wishlists.user_id = :user_id AND books.status = 'published'
             ORDER BY wishlists.created_at DESC"
        );
        $query->execute(['user_id' => $userId]);
        return $query->fetchAll();
    }

    public function add(int $userId, int $bookId): void
    {
        $query = $this->database->connection()->prepare(
            'INSERT INTO wishlists (user_id, book_id) VALUES (:user_id, :book_id)
             ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP'
        );
        $query->execute(['user_id' => $userId, 'book_id' => $bookId]);
    }

    public function remove(int $userId, int $bookId): void
    {
        $query = $this->database->connection()->prepare('DELETE FROM wishlists WHERE user_id = :user_id AND book_id = :book_id');
        $query->execute(['user_id' => $userId, 'book_id' => $bookId]);
    }
}
