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

    public function findByGoogleId(string $googleId): ?array
    {
        $statement = $this->database->connection()->prepare('SELECT * FROM users WHERE google_id = :google_id LIMIT 1');
        $statement->execute(['google_id' => $googleId]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    public function createVerificationToken(int $id): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $statement = $this->database->connection()->prepare(
            'UPDATE users SET email_verification_token_hash = :token_hash,
             email_verification_expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR),
             email_verified_at = NULL WHERE id = :id'
        );
        $statement->execute(['token_hash' => $hash, 'id' => $id]);
        return $token;
    }

    public function verifyEmail(string $token): bool
    {
        $hash = hash('sha256', trim($token));
        $statement = $this->database->connection()->prepare(
            'UPDATE users SET email_verified_at = NOW(), email_verification_token_hash = NULL,
             email_verification_expires_at = NULL
             WHERE email_verification_token_hash = :token_hash
             AND email_verification_expires_at >= NOW()
             AND email_verified_at IS NULL'
        );
        $statement->execute(['token_hash' => $hash]);
        return $statement->rowCount() > 0;
    }

    public function createPasswordResetToken(int $id): string
    {
        $token = bin2hex(random_bytes(32));
        $pdo = $this->database->connection();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM password_reset_tokens WHERE user_id = :user_id OR expires_at < NOW()')
                ->execute(['user_id' => $id]);
            $pdo->prepare(
                'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at)
                 VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 60 MINUTE))'
            )->execute(['user_id' => $id, 'token_hash' => hash('sha256', $token)]);
            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
        return $token;
    }

    public function resetPassword(string $token, string $password): bool
    {
        $pdo = $this->database->connection();
        $pdo->beginTransaction();
        try {
            $statement = $pdo->prepare(
                'SELECT user_id FROM password_reset_tokens
                 WHERE token_hash = :token_hash AND expires_at >= NOW() AND used_at IS NULL
                 LIMIT 1 FOR UPDATE'
            );
            $statement->execute(['token_hash' => hash('sha256', trim($token))]);
            $reset = $statement->fetch();
            if (!$reset) {
                $pdo->rollBack();
                return false;
            }

            $pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id')->execute([
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'id' => (int)$reset['user_id'],
            ]);
            $pdo->prepare('UPDATE password_reset_tokens SET used_at = NOW() WHERE token_hash = :token_hash')->execute([
                'token_hash' => hash('sha256', trim($token)),
            ]);
            $pdo->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }

    public function linkGoogleId(int $id, string $googleId): void
    {
        $statement = $this->database->connection()->prepare('UPDATE users SET google_id = :google_id WHERE id = :id');
        $statement->execute(['google_id' => $googleId, 'id' => $id]);
    }

    public function createFromGoogle(string $name, string $email, string $googleId): int
    {
        $statement = $this->database->connection()->prepare(
            "INSERT INTO users (name, email, email_verified_at, password_hash, google_id, customer_tier_id, referral_code)
             VALUES (:name, :email, NOW(), :password_hash, :google_id, (SELECT id FROM customer_tiers WHERE code = 'regular' LIMIT 1), NULL)"
        );
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
            'google_id' => $googleId,
        ]);
        $id = (int)$this->database->connection()->lastInsertId();
        $code = 'MB' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
        $this->database->connection()->prepare('UPDATE users SET referral_code = :referral_code WHERE id = :id')->execute(['referral_code' => $code, 'id' => $id]);
        return $id;
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
