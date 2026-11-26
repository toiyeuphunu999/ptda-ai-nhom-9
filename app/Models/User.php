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
        $databaseConnection = $this->database->connection();
        $databaseConnection->beginTransaction();
        try {
            $databaseConnection->prepare('DELETE FROM password_reset_tokens WHERE user_id = :user_id OR expires_at < NOW()')
                ->execute(['user_id' => $id]);
            $databaseConnection->prepare(
                'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at)
                 VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 60 MINUTE))'
            )->execute(['user_id' => $id, 'token_hash' => hash('sha256', $token)]);
            $databaseConnection->commit();
        } catch (\Throwable $exception) {
            if ($databaseConnection->inTransaction()) $databaseConnection->rollBack();
            throw $exception;
        }
        return $token;
    }

    public function resetPassword(string $token, string $password): bool
    {
        $databaseConnection = $this->database->connection();
        $databaseConnection->beginTransaction();
        try {
            $statement = $databaseConnection->prepare(
                'SELECT user_id FROM password_reset_tokens
                 WHERE token_hash = :token_hash AND expires_at >= NOW() AND used_at IS NULL
                 LIMIT 1 FOR UPDATE'
            );
            $statement->execute(['token_hash' => hash('sha256', trim($token))]);
            $reset = $statement->fetch();
            if (!$reset) {
                $databaseConnection->rollBack();
                return false;
            }

            $databaseConnection->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id')->execute([
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'id' => (int)$reset['user_id'],
            ]);
            $databaseConnection->prepare('UPDATE password_reset_tokens SET used_at = NOW() WHERE token_hash = :token_hash')->execute([
                'token_hash' => hash('sha256', trim($token)),
            ]);
            $databaseConnection->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($databaseConnection->inTransaction()) $databaseConnection->rollBack();
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

    public function updateProfile(int $id, string $name, string $phone = '', string $address = ''): void
    {
        $name = trim($name);
        $phone = trim($phone);
        $address = trim($address);
        if ($name === '' || mb_strlen($name) > 120) {
            throw new \RuntimeException('Họ tên không được để trống và tối đa 120 ký tự.');
        }
        if ($phone !== '' && !preg_match('/^[0-9+().\s-]{8,30}$/', $phone)) throw new \RuntimeException('Số điện thoại không hợp lệ.');
        if (mb_strlen($address) > 255) throw new \RuntimeException('Địa chỉ tối đa 255 ký tự.');
        $statement = $this->database->connection()->prepare('UPDATE users SET name = :name, phone = :phone, address = :address WHERE id = :id');
        $statement->execute(['name' => $name, 'phone' => $phone !== '' ? $phone : null, 'address' => $address !== '' ? $address : null, 'id' => $id]);
    }

    public function allForAdmin(): array
    {
        return $this->database->connection()->query(
            "SELECT users.id, users.name, users.email, users.phone, users.address, users.role,
                    users.wallet_balance, users.purchase_blocked, customer_tiers.name AS customer_tier,
                    users.created_at, COUNT(DISTINCT orders.id) AS order_count,
                    COALESCE(SUM(CASE WHEN orders.status = 'completed' THEN orders.total ELSE 0 END), 0) AS total_spent
             FROM users INNER JOIN customer_tiers ON customer_tiers.id = users.customer_tier_id
             LEFT JOIN orders ON orders.user_id = users.id
             GROUP BY users.id, users.name, users.email, users.phone, users.address, users.role,
                      users.wallet_balance, users.purchase_blocked, customer_tiers.name, users.created_at
             ORDER BY users.created_at DESC"
        )->fetchAll();
    }

    public function adminDetails(int $id): ?array
    {
        $statement = $this->database->connection()->prepare(
            "SELECT users.*, customer_tiers.name AS customer_tier,
                    COUNT(DISTINCT orders.id) AS order_count,
                    COALESCE(SUM(CASE WHEN orders.status = 'completed' THEN orders.total ELSE 0 END), 0) AS total_spent
             FROM users INNER JOIN customer_tiers ON customer_tiers.id = users.customer_tier_id
             LEFT JOIN orders ON orders.user_id = users.id
             WHERE users.id = :id
             GROUP BY users.id, customer_tiers.name
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    public function updateByAdmin(int $id, string $name, string $email, string $phone = '', string $address = ''): void
    {
        $name = trim($name); $email = trim($email); $phone = trim($phone); $address = trim($address);
        if ($name === '' || mb_strlen($name) > 120) throw new \RuntimeException('Họ tên không được để trống và tối đa 120 ký tự.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) throw new \RuntimeException('Email không hợp lệ.');
        if ($phone !== '' && !preg_match('/^[0-9+().\s-]{8,30}$/', $phone)) throw new \RuntimeException('Số điện thoại không hợp lệ.');
        if (mb_strlen($address) > 255) throw new \RuntimeException('Địa chỉ tối đa 255 ký tự.');
        $statement = $this->database->connection()->prepare(
            'UPDATE users SET name = :name, email = :email, phone = :phone, address = :address WHERE id = :id'
        );
        try {
            $statement->execute(['name'=>$name, 'email'=>$email, 'phone'=>$phone !== '' ? $phone : null, 'address'=>$address !== '' ? $address : null, 'id'=>$id]);
        } catch (\PDOException $exception) {
            if ((int)$exception->errorInfo[1] === 1062) throw new \RuntimeException('Email này đã được sử dụng bởi tài khoản khác.');
            throw $exception;
        }
    }

    public function setPurchaseBlocked(int $id, bool $blocked): void
    {
        $account = $this->findById($id);
        if (!$account) throw new \RuntimeException('Không tìm thấy tài khoản.');
        if (($account['role'] ?? '') !== 'customer') throw new \RuntimeException('Chỉ có thể khóa quyền mua của tài khoản khách hàng.');
        $statement = $this->database->connection()->prepare('UPDATE users SET purchase_blocked = :blocked WHERE id = :id');
        $statement->execute(['blocked' => $blocked ? 1 : 0, 'id' => $id]);
    }

    public function walletHistory(int $id): array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT wt.amount, wt.balance_after, wt.description, wt.created_at, creator.name AS created_by_name
             FROM wallet_transactions wt LEFT JOIN users creator ON creator.id = wt.created_by
             WHERE wt.user_id = :id ORDER BY wt.created_at DESC, wt.id DESC LIMIT 30'
        );
        $statement->execute(['id' => $id]);
        return $statement->fetchAll();
    }

    public function adjustWallet(int $id, int $amount, string $description, ?int $adminId = null): void
    {
        if ($amount === 0) throw new \RuntimeException('Số tiền điều chỉnh phải khác 0.');
        $description = trim($description);
        if ($description === '' || mb_strlen($description) > 255) throw new \RuntimeException('Vui lòng nhập lý do, tối đa 255 ký tự.');
        $databaseConnection = $this->database->connection();
        $databaseConnection->beginTransaction();
        try {
            $statement = $databaseConnection->prepare('SELECT wallet_balance FROM users WHERE id = :id FOR UPDATE');
            $statement->execute(['id' => $id]);
            $balance = $statement->fetchColumn();
            if ($balance === false) throw new \RuntimeException('Không tìm thấy tài khoản.');
            $newBalance = (int)$balance + $amount;
            if ($newBalance < 0) throw new \RuntimeException('Không thể trừ vượt quá số dư ví hiện tại.');
            $databaseConnection->prepare('UPDATE users SET wallet_balance = :balance WHERE id = :id')->execute(['balance'=>$newBalance, 'id'=>$id]);
            $databaseConnection->prepare(
                "INSERT INTO wallet_transactions (user_id, amount, balance_after, transaction_type, description, created_by)
                 VALUES (:user_id, :amount, :balance_after, 'admin_adjustment', :description, :created_by)"
            )->execute(['user_id'=>$id, 'amount'=>$amount, 'balance_after'=>$newBalance, 'description'=>$description, 'created_by'=>$adminId]);
            $databaseConnection->commit();
        } catch (\Throwable $exception) {
            if ($databaseConnection->inTransaction()) $databaseConnection->rollBack();
            throw $exception;
        }
    }

    public function canPurchase(int $id): bool
    {
        $statement = $this->database->connection()->prepare('SELECT role, purchase_blocked FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();
        return is_array($user) && (int)$user['purchase_blocked'] === 0;
    }

    public function countAll(): int
    {
        return (int)$this->database->connection()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }
}
