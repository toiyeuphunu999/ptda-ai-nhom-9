<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DateTime;
use DateTimeZone;
use RuntimeException;

final class Voucher
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function allForAdmin(): array
    {
        return $this->database->connection()->query('SELECT * FROM vouchers ORDER BY created_at DESC')->fetchAll();
    }

    public function findByCode(string $code): ?array
    {
        $statement = $this->database->connection()->prepare('SELECT * FROM vouchers WHERE code = :code LIMIT 1');
        $code = strtoupper(trim($code));
        $statement->execute(['code' => $code]);
        $row = $statement->fetch();

        if (!$row) {
            return null;
        }

        return $row;
    }

    public static function isUsable(array $voucher, int $subtotal): bool
    {
        $timezone = new DateTimeZone('Asia/Bangkok');
        $now = new DateTime('now', $timezone);
        if ($voucher['status'] !== 'active') {
            return false;
        }

        if ($subtotal < (int)$voucher['min_order_amount']) {
            return false;
        }

        if ($voucher['starts_at'] && new DateTime($voucher['starts_at'], $timezone) > $now) {
            return false;
        }

        if ($voucher['expires_at'] && new DateTime($voucher['expires_at'], $timezone) < $now) {
            return false;
        }

        if ($voucher['usage_limit'] !== null && (int)$voucher['used_count'] >= (int)$voucher['usage_limit']) {
            return false;
        }

        if ($voucher['type'] === 'percent') {
            return (int)$voucher['value'] >= 1 && (int)$voucher['value'] <= 100;
        }

        return (int)$voucher['value'] > 0;
    }

    public static function discountFor(array $voucher, int $subtotal): int
    {
        if (!self::isUsable($voucher, $subtotal)) {
            throw new RuntimeException('Mã giảm giá không còn hiệu lực hoặc chưa đủ điều kiện.');
        }
        $discount = (int)$voucher['value'];
        if ($voucher['type'] === 'percent') {
            $discount = (int)floor($subtotal * (int)$voucher['value'] / 100);
        }

        return min($subtotal, max(0, $discount));
    }

    public function create(string $code, string $type, int $value, int $minimum, ?string $startsAt, ?string $expiresAt, ?int $usageLimit): void
    {
        $code = strtoupper(trim($code));
        $validCode = preg_match('/^[A-Z0-9_-]{3,50}$/', $code) === 1;
        $validType = in_array($type, ['percent', 'fixed'], true);
        $validPercent = $type !== 'percent' || $value <= 100;
        $validUsageLimit = $usageLimit === null || $usageLimit >= 1;
        if (!$validCode || !$validType || $value < 1 || !$validPercent || $minimum < 0 || !$validUsageLimit) {
            throw new RuntimeException('Mã, loại và giá trị voucher không hợp lệ.');
        }
        $invalidStart = $startsAt !== null && strtotime($startsAt) === false;
        $invalidEnd = $expiresAt !== null && strtotime($expiresAt) === false;
        if ($invalidStart || $invalidEnd) {
            throw new RuntimeException('Thời hạn voucher không hợp lệ.');
        }

        if ($startsAt && $expiresAt && strtotime($expiresAt) < strtotime($startsAt)) {
            throw new RuntimeException('Thời hạn voucher không hợp lệ.');
        }
        $statement = $this->database->connection()->prepare('INSERT INTO vouchers (code,type,value,min_order_amount,starts_at,expires_at,usage_limit) VALUES (:code,:type,:value,:minimum,:starts_at,:expires_at,:usage_limit)');
        $startValue = $startsAt === '' ? null : $startsAt;
        $endValue = $expiresAt === '' ? null : $expiresAt;
        $limitValue = $usageLimit !== null && $usageLimit > 0 ? $usageLimit : null;
        $parameters = [
            'code' => $code,
            'type' => $type,
            'value' => $value,
            'minimum' => max(0, $minimum),
            'starts_at' => $startValue,
            'expires_at' => $endValue,
            'usage_limit' => $limitValue,
        ];
        $statement->execute($parameters);
    }

    public function delete(int $id): void
    {
        $statement = $this->database->connection()->prepare('DELETE FROM vouchers WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
