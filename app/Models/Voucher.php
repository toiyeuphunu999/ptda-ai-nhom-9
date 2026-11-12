<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DateTime;
use RuntimeException;

final class Voucher
{
    public function __construct(private Database $database) {}

    public function allForAdmin(): array
    {
        return $this->database->connection()->query('SELECT * FROM vouchers ORDER BY created_at DESC')->fetchAll();
    }

    public function findByCode(string $code): ?array
    {
        $statement = $this->database->connection()->prepare('SELECT * FROM vouchers WHERE code = :code LIMIT 1');
        $statement->execute(['code' => strtoupper(trim($code))]);
        return ($row = $statement->fetch()) ? $row : null;
    }

    public static function isUsable(array $voucher, int $subtotal): bool
    {
        $now = new DateTime();
        return $voucher['status'] === 'active'
            && $subtotal >= (int)$voucher['min_order_amount']
            && (!$voucher['starts_at'] || new DateTime($voucher['starts_at']) <= $now)
            && (!$voucher['expires_at'] || new DateTime($voucher['expires_at']) >= $now)
            && ($voucher['usage_limit'] === null || (int)$voucher['used_count'] < (int)$voucher['usage_limit'])
            && ($voucher['type'] === 'percent' ? (int)$voucher['value'] >= 1 && (int)$voucher['value'] <= 100 : (int)$voucher['value'] > 0);
    }

    public static function discountFor(array $voucher, int $subtotal): int
    {
        if (!self::isUsable($voucher, $subtotal)) throw new RuntimeException('Mã giảm giá không còn hiệu lực hoặc chưa đủ điều kiện.');
        $discount = $voucher['type'] === 'percent'
            ? (int)floor($subtotal * (int)$voucher['value'] / 100)
            : (int)$voucher['value'];
        return min($subtotal, max(0, $discount));
    }

    public function create(string $code, string $type, int $value, int $minimum, ?string $startsAt, ?string $expiresAt, ?int $usageLimit): void
    {
        $code = strtoupper(trim($code));
        if (!preg_match('/^[A-Z0-9_-]{3,50}$/', $code) || !in_array($type, ['percent', 'fixed'], true) || $value < 1 || ($type === 'percent' && $value > 100)) {
            throw new RuntimeException('Mã, loại và giá trị voucher không hợp lệ.');
        }
        if ($startsAt && $expiresAt && strtotime($expiresAt) < strtotime($startsAt)) throw new RuntimeException('Thời hạn voucher không hợp lệ.');
        $statement = $this->database->connection()->prepare('INSERT INTO vouchers (code,type,value,min_order_amount,starts_at,expires_at,usage_limit) VALUES (:code,:type,:value,:minimum,:starts_at,:expires_at,:usage_limit)');
        $statement->execute(['code'=>$code,'type'=>$type,'value'=>$value,'minimum'=>max(0,$minimum),'starts_at'=>$startsAt ?: null,'expires_at'=>$expiresAt ?: null,'usage_limit'=>$usageLimit && $usageLimit > 0 ? $usageLimit : null]);
    }

    public function delete(int $id): void
    {
        $statement = $this->database->connection()->prepare('DELETE FROM vouchers WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
