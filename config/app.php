<?php

declare(strict_types=1);

return [
    'name' => 'Nhà sách Mộc Bản',
    'base_url' => '/',
    'production' => true,
    'payos' => [
        'client_id' => getenv('PAYOS_CLIENT_ID') ?: '',
        'api_key' => getenv('PAYOS_API_KEY') ?: '',
        'checksum_key' => getenv('PAYOS_CHECKSUM_KEY') ?: '',
        'api_url' => 'https://api-merchant.payos.vn',
    ],
    'google' => [
        'client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
        'redirect_uri' => getenv('GOOGLE_REDIRECT_URI') ?: ((getenv('APP_URL') ?: 'http://localhost') . '/auth/google/callback'),
    ],
    'mail' => [
        'from' => getenv('MAIL_FROM') ?: 'no-reply@localhost',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'Nhà sách Mộc Bản',
    ],
];
