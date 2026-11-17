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

    public function create(string $name, string $email, string $password, string $referralCode = ''): int
    {
        $referrerId = null;
        if (trim($referralCode) !== '') {
            $referrerStatement = $this->database->connection()->prepare('SELECT id FROM users WHERE referral_code = :referral_code LIMIT 1');
            $referrerStatement->execute(['referral_code' => strtoupper(trim($referralCode))]);
            $referrerId = $referrerStatement->fetchColumn() ?: null;
        }
        $statement = $this->database->connection()->prepare(
            "INSERT INTO users (name, email, password_hash, customer_tier_id, referral_code, referred_by)
             VALUES (:name, :email, :password_hash, (SELECT id FROM customer_tiers WHERE code = 'regular' LIMIT 1), NULL, :referred_by)"
        );
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'referred_by' => $referrerId,
        ]);
        $id = (int)$this->database->connection()->lastInsertId();
        $code = 'MB' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
        $this->database->connection()->prepare('UPDATE users SET referral_code = :referral_code WHERE id = :id')->execute(['referral_code' => $code, 'id' => $id]);
        return $id;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->database->connection()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    public function findByReferralCode(string $code): ?array
    {
        $statement = $this->database->connection()->prepare('SELECT id, name FROM users WHERE referral_code = :referral_code LIMIT 1');
        $statement->execute(['referral_code' => strtoupper(trim($code))]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    public function updateName(int $id, string $name): void
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 120) {
            throw new \RuntimeException('Họ tên không được để trống và tối đa 120 ký tự.');
        }
        $statement = $this->database->connection()->prepare('UPDATE users SET name = :name WHERE id = :id');
        $statement->execute(['name' => $name, 'id' => $id]);
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
