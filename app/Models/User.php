<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    public function __construct(private Database $database)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->database->connection()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    public function create(string $name, string $email, string $password): int
    {
        $statement = $this->database->connection()->prepare(
            "INSERT INTO users (name, email, password_hash, customer_tier_id)
             VALUES (:name, :email, :password_hash, (SELECT id FROM customer_tiers WHERE code = 'regular' LIMIT 1))"
        );
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        return (int)$this->database->connection()->lastInsertId();
    }

    public function allForAdmin(): array
    {
        return $this->database->connection()->query(
            'SELECT users.id, users.name, users.email, users.role, customer_tiers.name AS customer_tier, users.created_at
             FROM users INNER JOIN customer_tiers ON customer_tiers.id = users.customer_tier_id
             ORDER BY users.created_at DESC'
        )->fetchAll();
    }

    public function countAll(): int
    {
        return (int)$this->database->connection()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }
}
