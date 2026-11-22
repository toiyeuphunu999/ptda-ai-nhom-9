<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::put('_csrf_token', $token);
        }
        return $token;
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function verify(?string $token): void
    {
        if (!$token || !hash_equals(self::token(), $token)) {
            throw new RuntimeException('Yêu cầu không hợp lệ. Vui lòng thử lại.');
        }
    }
}
