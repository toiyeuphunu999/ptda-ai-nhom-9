<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class PayOSService
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function isConfigured(): bool
    {
        return $this->config['client_id'] !== ''
            && $this->config['api_key'] !== ''
            && $this->config['checksum_key'] !== '';
    }

    public function createPaymentLink(int $orderCode, int $amount, string $description, string $buyerName, string $buyerPhone, string $buyerAddress, array $items): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Thanh toán PayOS chưa được cấu hình.');
        }
        $appUrl = rtrim((string)($this->config['app_url'] ?? getenv('APP_URL') ?: 'http://localhost'), '/');
        $returnUrl = $appUrl . '/payment/payos/return';
        $cancelUrl = $appUrl . '/payment/payos/cancel';
        $paymentItems = [];
        foreach ($items as $item) {
            $paymentItems[] = [
                'name' => (string)$item['name'],
                'quantity' => (int)$item['quantity'],
                'price' => (int)$item['price'],
            ];
        }
        $payload = [
            'orderCode' => $orderCode,
            'amount' => $amount,
            'description' => substr($description, 0, 25),
            'buyerName' => $buyerName,
            'buyerPhone' => $buyerPhone,
            'buyerAddress' => $buyerAddress,
            'items' => $paymentItems,
            'cancelUrl' => $cancelUrl,
            'returnUrl' => $returnUrl,
            'expiredAt' => time() + 900,
        ];
        $signatureData = sprintf(
            'amount=%d&cancelUrl=%s&description=%s&orderCode=%d&returnUrl=%s',
            $payload['amount'], $payload['cancelUrl'], $payload['description'], $payload['orderCode'], $payload['returnUrl']
        );
        $payload['signature'] = hash_hmac('sha256', $signatureData, $this->config['checksum_key']);
        $response = $this->request('/v2/payment-requests', $payload);
        if (($response['code'] ?? null) !== '00' || empty($response['data']['checkoutUrl'])) {
            throw new RuntimeException((string)($response['desc'] ?? 'Không tạo được link thanh toán PayOS.'));
        }
        return $response;
    }

    public function verifyWebhook(array $payload): bool
    {
        if (!$this->isConfigured() || !isset($payload['data'], $payload['signature']) || !is_array($payload['data'])) {
            return false;
        }
        $data = $payload['data'];
        ksort($data);
        $parts = [];
        foreach ($data as $key => $value) {
            if ($value === null || $value === 'null' || $value === 'undefined') $value = '';
            if (is_array($value)) {
                foreach ($value as &$element) {
                    if (is_array($element)) {
                        ksort($element);
                    }
                }
                unset($element);
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } elseif (is_bool($value)) {
                $value = $value ? '1' : '';
            }
            $parts[] = $key . '=' . (string)$value;
        }
        $signature = hash_hmac('sha256', implode('&', $parts), $this->config['checksum_key']);
        return hash_equals(strtolower($signature), strtolower((string)$payload['signature']));
    }

    private function request(string $path, array $payload): array
    {
        $handle = curl_init(rtrim($this->config['api_url'], '/') . $path);
        if ($handle === false) {
            throw new RuntimeException('Không khởi tạo được kết nối PayOS.');
        }
        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-client-id: ' . $this->config['client_id'],
                'x-api-key: ' . $this->config['api_key'],
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
        ]);
        $body = curl_exec($handle);
        $status = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $error = curl_error($handle);
        curl_close($handle);
        if ($body === false || $error !== '') {
            throw new RuntimeException('Không thể kết nối PayOS: ' . $error);
        }

        $decoded = json_decode((string)$body, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('PayOS trả về dữ liệu không hợp lệ (HTTP ' . $status . ').');
        }
        return $decoded;
    }
}
