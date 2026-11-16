<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use RuntimeException;

final class SystemSetting
{
    private const PAYOS_API_URL = 'https://api-merchant.payos.vn';

    public function __construct(private Database $database) {}

    public function allForAdmin(): array
    {
        $rows = $this->database->connection()->query('SELECT setting_key, setting_value, is_secret, updated_at FROM system_settings ORDER BY setting_key')->fetchAll();
        return array_map(static function (array $row): array {
            if ((int)$row['is_secret'] === 1) $row['setting_value'] = $row['setting_value'] ? '••••••••' : '';
            return $row;
        }, $rows);
    }

    public function payOSConfig(): array
    {
        $get = function (string $key): string {
            $stored = $this->get($key);
            return ($stored !== null && $stored !== '') ? $stored : (string)(getenv($key) ?: '');
        };
        return [
            'client_id' => $get('PAYOS_CLIENT_ID'),
            'api_key' => $get('PAYOS_API_KEY'),
            'checksum_key' => $get('PAYOS_CHECKSUM_KEY'),
            'api_url' => self::PAYOS_API_URL,
            'app_url' => $this->currentHttpHost(),
        ];
    }

    private function currentHttpHost(): string
    {
        $host = preg_replace('/[^a-zA-Z0-9.:\-\[\]]/', '', (string)($_SERVER['HTTP_HOST'] ?? ''));
        return 'http://' . ($host !== '' ? $host : 'localhost');
    }

    public function get(string $key): ?string
    {
        $statement = $this->database->connection()->prepare('SELECT setting_value, is_secret FROM system_settings WHERE setting_key = :setting_key LIMIT 1');
        $statement->execute(['setting_key' => $key]);
        $row = $statement->fetch();
        if (!$row) return null;
        return (int)$row['is_secret'] === 1 ? $this->decrypt((string)$row['setting_value']) : (string)$row['setting_value'];
    }

    public function saveMany(array $settings, int $updatedBy): void
    {
        $pdo = $this->database->connection();
        $pdo->beginTransaction();
        try {
            $statement = $pdo->prepare('INSERT INTO system_settings (setting_key, setting_value, is_secret, updated_by) VALUES (:setting_key, :setting_value, :is_secret, :updated_by) ON DUPLICATE KEY UPDATE setting_value = :updated_value, is_secret = :updated_secret, updated_by = :updated_by_update');
            foreach ($settings as $key => $data) {
                $value = (string)($data['value'] ?? '');
                $secret = (bool)($data['secret'] ?? false);
                // Ô secret để trống luôn giữ nguyên cấu hình cũ; nếu chưa có thì không tạo bản ghi rỗng.
                if ($secret && trim($value) === '') continue;
                $stored = $secret ? $this->encrypt($value) : $value;
                $statement->execute(['setting_key'=>$key,'setting_value'=>$stored,'is_secret'=>$secret?1:0,'updated_by'=>$updatedBy,'updated_value'=>$stored,'updated_secret'=>$secret?1:0,'updated_by_update'=>$updatedBy]);
            }
            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }

    private function encryptionKey(): string
    {
        $appKey = (string)(getenv('APP_KEY') ?: '');
        if ($appKey === '') {
            $keyFile = dirname(__DIR__, 2) . '/storage/app.key';
            if (is_file($keyFile)) {
                $appKey = trim((string)file_get_contents($keyFile));
            } else {
                $appKey = base64_encode(random_bytes(32));
                $storage = dirname($keyFile);
                if (!is_dir($storage)) mkdir($storage, 0750, true);
                if (file_put_contents($keyFile, $appKey, LOCK_EX) === false) {
                    throw new RuntimeException('Không thể tạo khóa bảo mật local. Hãy cấu hình APP_KEY trên máy chủ.');
                }
                @chmod($keyFile, 0600);
            }
        }
        if ($appKey === '') throw new RuntimeException('Khóa bảo mật không hợp lệ.');
        return hash('sha256', $appKey, true);
    }

    private function encrypt(string $value): string
    {
        $key = $this->encryptionKey();
        if (function_exists('sodium_crypto_secretbox')) {
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            return 'enc:s:' . base64_encode($nonce . sodium_crypto_secretbox($value, $nonce, $key));
        }
        if (!function_exists('openssl_encrypt')) throw new RuntimeException('PHP cần bật Sodium hoặc OpenSSL extension.');
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($value, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) throw new RuntimeException('Không thể mã hóa cấu hình hệ thống.');
        return 'enc:o:' . base64_encode($iv . $tag . $cipher);
    }

    private function decrypt(string $value): string
    {
        $key = $this->encryptionKey();
        if (str_starts_with($value, 'enc:s:') && function_exists('sodium_crypto_secretbox_open')) {
            $raw = base64_decode(substr($value, 6), true);
            if ($raw === false || strlen($raw) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) return '';
            $plain = sodium_crypto_secretbox_open(substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES), substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES), $key);
            return $plain === false ? '' : $plain;
        }
        if (str_starts_with($value, 'enc:o:') && function_exists('openssl_decrypt')) {
            $raw = base64_decode(substr($value, 6), true);
            if ($raw === false || strlen($raw) <= 28) return '';
            $iv = substr($raw, 0, 12);
            $tag = substr($raw, 12, 16);
            $cipher = substr($raw, 28);
            $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
            return $plain === false ? '' : $plain;
        }
        return '';
    }
}
