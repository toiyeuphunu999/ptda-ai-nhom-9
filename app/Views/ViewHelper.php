<?php

declare(strict_types=1);

namespace App\Helpers;

final class ViewHelper
{
    public static function escape(string $value): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}(?::\d{2})?)?$/', $value)) {
            try {
                $date = new \DateTimeImmutable($value);
                $value = strlen($value) > 10 ? $date->format('d/m/Y H:i') : $date->format('d/m/Y');
            } catch (\Throwable) {
                // Giữ nguyên nếu dữ liệu ngày không hợp lệ.
            }
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function money(int $value): string
    {
        return number_format($value, 0, ',', '.') . ' đ';
    }

    public static function richText(string $value): string
    {
        $allowedTags = '<p><br><strong><em><u><s><ol><ul><li><blockquote>';
        return strip_tags($value, $allowedTags);
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

    public static function bookStatus(string $status): string
    {
        return [
            'draft' => 'Bản nháp',
            'published' => 'Đang bán',
            'hidden' => 'Đã ẩn',
        ][$status] ?? $status;
    }

    public static function userRole(string $role): string
    {
        return [
            'customer' => 'Khách hàng',
            'admin' => 'Quản trị viên',
        ][$role] ?? $role;
    }
}
