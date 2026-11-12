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
        'api_url' => getenv('PAYOS_API_URL') ?: 'https://api-merchant.payos.vn',
    ],
];
