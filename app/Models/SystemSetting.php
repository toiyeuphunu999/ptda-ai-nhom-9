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
        $settings = array();
        foreach ($rows as $row) {
            if ((int)$row['is_secret'] === 1) {
                $row['setting_value'] = $row['setting_value'] ? '••••••••' : '';
            }
            $settings[] = $row;
        }
        return $settings;
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

    public function googleConfig(): array
    {
        $get = function (string $key): string {
            $stored = $this->get($key);
            return ($stored !== null && $stored !== '') ? $stored : (string)(getenv($key) ?: '');
        };
        return [
            'client_id' => $get('GOOGLE_CLIENT_ID'),
            'client_secret' => $get('GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => $get('GOOGLE_REDIRECT_URI') ?: ($this->currentHttpHost() . '/auth/google/callback'),
        ];
    }

    public function mailConfig(): array
    {
        $get = function (string $key): string {
            $stored = $this->get($key);
            return ($stored !== null && $stored !== '') ? $stored : (string)(getenv($key) ?: '');
        };
        return [
            'host' => $get('MAIL_SMTP_HOST'),
            'port' => (int)($get('MAIL_SMTP_PORT') ?: 587),
            'username' => $get('MAIL_SMTP_USERNAME'),
            'password' => $get('MAIL_SMTP_PASSWORD'),
            'encryption' => strtolower($get('MAIL_SMTP_ENCRYPTION') ?: 'tls'),
            'from' => $get('MAIL_FROM') ?: 'no-reply@localhost',
            'from_name' => $get('MAIL_FROM_NAME') ?: 'Nhà sách Mộc Bản',
        ];
    }

    private function currentHttpHost(): string
    {
        return rtrim((string)(getenv('APP_URL') ?: 'http://localhost'), '/');
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

    public function dataFromInput(array $input): array
    {
        $shopPhone = trim((string)($input['shop_phone'] ?? ''));
        $shopEmail = trim((string)($input['shop_email'] ?? ''));
        $payosClientId = trim((string)($input['payos_client_id'] ?? ''));
        $payosApiKey = trim((string)($input['payos_api_key'] ?? ''));
        $payosChecksumKey = trim((string)($input['payos_checksum_key'] ?? ''));
        $googleClientId = trim((string)($input['google_client_id'] ?? ''));
        $googleClientSecret = trim((string)($input['google_client_secret'] ?? ''));
        $googleRedirectUri = trim((string)($input['google_redirect_uri'] ?? ''));
        $mailFrom = trim((string)($input['mail_from'] ?? ''));
        $mailFromName = trim((string)($input['mail_from_name'] ?? ''));
        $smtpHost = trim((string)($input['mail_smtp_host'] ?? ''));
        $smtpUsername = trim((string)($input['mail_smtp_username'] ?? ''));
        $smtpPassword = trim((string)($input['mail_smtp_password'] ?? ''));
        $smtpEncryption = $input['mail_smtp_encryption'] ?? 'tls';
        if (!in_array($smtpEncryption, array('tls', 'ssl', 'none'), true)) {
            $smtpEncryption = 'tls';
        }
        if ($shopEmail !== '' && !filter_var($shopEmail, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Email cửa hàng không hợp lệ.');
        if ($mailFrom !== '' && !filter_var($mailFrom, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Email gửi đi không hợp lệ.');
        if ($googleRedirectUri !== '' && !filter_var($googleRedirectUri, FILTER_VALIDATE_URL)) throw new RuntimeException('Địa chỉ callback Google không hợp lệ.');
        if ($smtpHost !== '' && mb_strlen($smtpHost) > 190) throw new RuntimeException('Tên máy chủ SMTP quá dài.');
        if ((int)($input['mail_smtp_port'] ?? 587) < 1 || (int)($input['mail_smtp_port'] ?? 587) > 65535) throw new RuntimeException('Cổng SMTP không hợp lệ.');
        if ((int)($input['shipping_fee'] ?? 0) < 0) throw new RuntimeException('Phí vận chuyển không hợp lệ.');
        return [
            'SHOP_PHONE' => ['value' => $shopPhone, 'secret' => false],
            'SHOP_EMAIL' => ['value' => $shopEmail, 'secret' => false],
            'SHIPPING_FEE' => ['value' => (string)max(0, (int)($input['shipping_fee'] ?? 0)), 'secret' => false],
            'PAYOS_CLIENT_ID' => ['value' => $payosClientId, 'secret' => true],
            'PAYOS_API_KEY' => ['value' => $payosApiKey, 'secret' => true],
            'PAYOS_CHECKSUM_KEY' => ['value' => $payosChecksumKey, 'secret' => true],
            'GOOGLE_CLIENT_ID' => ['value' => $googleClientId, 'secret' => true],
            'GOOGLE_CLIENT_SECRET' => ['value' => $googleClientSecret, 'secret' => true],
            'GOOGLE_REDIRECT_URI' => ['value' => $googleRedirectUri, 'secret' => false],
            'MAIL_FROM' => ['value' => $mailFrom, 'secret' => false],
            'MAIL_FROM_NAME' => ['value' => $mailFromName, 'secret' => false],
            'MAIL_SMTP_HOST' => ['value' => $smtpHost, 'secret' => false],
            'MAIL_SMTP_PORT' => ['value' => (string)max(1, (int)($input['mail_smtp_port'] ?? 587)), 'secret' => false],
            'MAIL_SMTP_USERNAME' => ['value' => $smtpUsername, 'secret' => false],
            'MAIL_SMTP_PASSWORD' => ['value' => $smtpPassword, 'secret' => true],
            'MAIL_SMTP_ENCRYPTION' => ['value' => $smtpEncryption, 'secret' => false],
        ];
    }

    public function adminViewData(?string $error = null): array
    {
        $payos = $this->payOSConfig();
        $google = $this->googleConfig();
        $mail = $this->mailConfig();
        return [
            'title' => 'Cài đặt hệ thống',
            'settings' => $this->allForAdmin(),
            'payosConfig' => $payos,
            'payosConfigured' => trim((string)($payos['client_id'] ?? '')) !== '' && trim((string)($payos['api_key'] ?? '')) !== '' && trim((string)($payos['checksum_key'] ?? '')) !== '',
            'googleConfig' => $google,
            'googleConfigured' => trim((string)($google['client_id'] ?? '')) !== '' && trim((string)($google['client_secret'] ?? '')) !== '' && trim((string)($google['redirect_uri'] ?? '')) !== '',
            'mailConfig' => $mail,
            'mailConfigured' => trim((string)($mail['host'] ?? '')) !== '' && filter_var((string)($mail['from'] ?? ''), FILTER_VALIDATE_EMAIL) !== false,
            'error' => $error,
        ];
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
