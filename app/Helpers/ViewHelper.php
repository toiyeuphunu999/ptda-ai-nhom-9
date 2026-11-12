<?php

declare(strict_types=1);

namespace App\Helpers;

final class ViewHelper
{
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function money(int $value): string
    {
        return number_format($value, 0, ',', '.') . ' đ';
    }

    public static function orderStatus(string $status): string
    {
        return [
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn tất',
            'cancelled' => 'Đã hủy',
        ][$status] ?? $status;
    }
}
