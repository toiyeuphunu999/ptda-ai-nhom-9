<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use RuntimeException;

final class UserAddress
{
    public function __construct(private Database $database) {}

    public function allForUser(int $userId): array
    {
        $query = $this->database->connection()->prepare('SELECT * FROM user_addresses WHERE user_id = :user_id ORDER BY is_default DESC, id DESC');
        $query->execute(['user_id' => $userId]);
        return $query->fetchAll();
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $query = $this->database->connection()->prepare('SELECT * FROM user_addresses WHERE id = :id AND user_id = :user_id LIMIT 1');
        $query->execute(['id' => $id, 'user_id' => $userId]);
        $address = $query->fetch();
        return $address ?: null;
    }

    public function create(int $userId, string $label, string $name, string $phone, string $address, bool $isDefault = false): int
    {
        [$label, $name, $phone, $address] = $this->validate($label, $name, $phone, $address);
        $pdo = $this->database->connection();
        $isDefault = $isDefault || !$this->allForUser($userId);
        $pdo->beginTransaction();
        try {
            if ($isDefault) $this->clearDefault($userId);
            $query = $pdo->prepare('INSERT INTO user_addresses (user_id, label, recipient_name, phone, address, is_default) VALUES (:user_id, :label, :recipient_name, :phone, :address, :is_default)');
            $query->execute(['user_id'=>$userId, 'label'=>$label, 'recipient_name'=>$name, 'phone'=>$phone, 'address'=>$address, 'is_default'=>$isDefault ? 1 : 0]);
            $id = (int)$pdo->lastInsertId(); $pdo->commit(); return $id;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack(); throw $exception;
        }
    }

    public function update(int $id, int $userId, string $label, string $name, string $phone, string $address): void
    {
        [$label, $name, $phone, $address] = $this->validate($label, $name, $phone, $address);
        $query = $this->database->connection()->prepare('UPDATE user_addresses SET label = :label, recipient_name = :recipient_name, phone = :phone, address = :address WHERE id = :id AND user_id = :user_id');
        $query->execute(['id'=>$id, 'user_id'=>$userId, 'label'=>$label, 'recipient_name'=>$name, 'phone'=>$phone, 'address'=>$address]);
        if ($query->rowCount() === 0 && !$this->findForUser($id, $userId)) throw new RuntimeException('Không tìm thấy địa chỉ cần cập nhật.');
    }

    public function setDefault(int $id, int $userId): void
    {
        if (!$this->findForUser($id, $userId)) throw new RuntimeException('Không tìm thấy địa chỉ cần chọn.');
        $pdo = $this->database->connection(); $pdo->beginTransaction();
        try {
            $this->clearDefault($userId);
            $query = $pdo->prepare('UPDATE user_addresses SET is_default = 1 WHERE id = :id AND user_id = :user_id');
            $query->execute(['id'=>$id, 'user_id'=>$userId]); $pdo->commit();
        } catch (\Throwable $exception) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $exception; }
    }

    public function delete(int $id, int $userId): void
    {
        $address = $this->findForUser($id, $userId);
        if (!$address) throw new RuntimeException('Không tìm thấy địa chỉ cần xóa.');
        $pdo = $this->database->connection(); $pdo->beginTransaction();
        try {
            $query = $pdo->prepare('DELETE FROM user_addresses WHERE id = :id AND user_id = :user_id');
            $query->execute(['id'=>$id, 'user_id'=>$userId]);
            if ((int)$address['is_default'] === 1) {
                $next = $this->allForUser($userId)[0] ?? null;
                if ($next) { $this->clearDefault($userId); $set = $pdo->prepare('UPDATE user_addresses SET is_default = 1 WHERE id = :id'); $set->execute(['id'=>(int)$next['id']]); }
            }
            $pdo->commit();
        } catch (\Throwable $exception) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $exception; }
    }

    private function clearDefault(int $userId): void
    {
        $query = $this->database->connection()->prepare('UPDATE user_addresses SET is_default = 0 WHERE user_id = :user_id');
        $query->execute(['user_id'=>$userId]);
    }

    private function validate(string $label, string $name, string $phone, string $address): array
    {
        $label=trim($label); $name=trim($name); $phone=trim($phone); $address=trim($address);
        if ($label === '' || mb_strlen($label) > 50) throw new RuntimeException('Tên địa chỉ không hợp lệ.');
        if ($name === '' || mb_strlen($name) > 120) throw new RuntimeException('Họ tên người nhận không hợp lệ.');
        if ($phone === '' || mb_strlen($phone) > 30) throw new RuntimeException('Số điện thoại không hợp lệ.');
        if ($address === '' || mb_strlen($address) > 255) throw new RuntimeException('Địa chỉ không hợp lệ.');
        return [$label, $name, $phone, $address];
    }
}
