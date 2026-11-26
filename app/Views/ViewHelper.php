<?php

declare(strict_types=1);

namespace App\Helpers;

final class ViewHelper
{
    public static function escape(mixed $value): string
    {
        if ($value === null) {
            $value = '';
        } elseif (!is_scalar($value) && !($value instanceof \Stringable)) {
            $value = '';
        } else {
            $value = (string)$value;
        }

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
        $value = strip_tags($value, $allowedTags);
        $value = preg_replace_callback(
            '/<([a-z][a-z0-9]*)\b[^>]*>/i',
            function (array $match): string {
                return '<' . strtolower($match[1]) . '>';
            },
            $value
        );
        return is_string($value) ? $value : '';
    }

    public static function orderStatus(string $status): string
    {
        return [
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping' => 'Đang giao',
            'delivered' => 'Giao thành công',
            'completed' => 'Hoàn tất',
            'cancel_requested' => 'Chờ duyệt hủy',
            'return_requested' => 'Chờ duyệt đổi trả',
            'return_shipping' => 'Đang trả hàng',
            'returned' => 'Đã nhận hàng trả',
            'return_rejected' => 'Từ chối đổi trả',
            'cancelled' => 'Đã hủy',
        ][$status] ?? $status;
    }

    public static function paymentMethod(string $method): string
    {
        return [
            'cod' => 'Thanh toán khi nhận hàng (COD)',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'momo' => 'Ví MoMo',
            'vnpay' => 'VNPay',
            'zalopay' => 'ZaloPay',
        ][$method] ?? $method;
    }

    public static function paymentStatus(string $status): string
    {
        return [
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            'cancelled' => 'Đã hủy thanh toán',
            'refunded' => 'Đã hoàn tiền',
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
