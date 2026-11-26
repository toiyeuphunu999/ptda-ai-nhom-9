<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class SmtpMailer
{
    private $connection;

    public function __construct(private array $config)
    {
    }

    public function send(string $to, string $subject, string $body, ?string $htmlBody = null): bool
    {
        $host = trim((string)($this->config['host'] ?? ''));
        $port = (int)($this->config['port'] ?? 587);
        $encryption = strtolower((string)($this->config['encryption'] ?? 'tls'));
        $from = trim((string)($this->config['from'] ?? ''));
        $fromName = (string)($this->config['from_name'] ?? '');
        if ($host === '' || $port < 1 || $from === '') throw new RuntimeException('SMTP chưa được cấu hình đầy đủ.');
        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || !filter_var($from, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Địa chỉ email SMTP không hợp lệ.');
        if (preg_match('/[\r\n]/', $host . $from . $fromName . $to . $subject) === 1) throw new RuntimeException('Thông tin email chứa ký tự không hợp lệ.');
        if (str_contains($host, '@') || filter_var($host, FILTER_VALIDATE_URL) !== false) {
            throw new RuntimeException('SMTP Host không hợp lệ. Hãy nhập hostname, ví dụ smtp.tino.vn.');
        }

        $address = $encryption === 'ssl' ? 'ssl://' . $host : $host;
        $errorNumber = 0; $errorMessage = '';
        $this->connection = @fsockopen($address, $port, $errorNumber, $errorMessage, 15);
        if (!$this->connection) throw new RuntimeException('Không thể kết nối SMTP: ' . $errorMessage);
        stream_set_timeout($this->connection, 15);
        try {
            $this->expect(220);
            $this->command('EHLO localhost', 250);
            if ($encryption === 'tls') {
                $this->command('STARTTLS', 220);
                if (!stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) throw new RuntimeException('Không bật được mã hóa TLS cho SMTP.');
                $this->command('EHLO localhost', 250);
            }
            $username = (string)($this->config['username'] ?? '');
            $password = (string)($this->config['password'] ?? '');
            if ($username !== '') {
                $this->command('AUTH LOGIN', 334);
                $this->command(base64_encode($username), 334);
                $this->command(base64_encode($password), 235);
            }
            $this->command('MAIL FROM:<' . $from . '>', 250);
            $this->command('RCPT TO:<' . $to . '>', 250);
            $this->command('DATA', 354);
            $encodedSubject = function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($subject, 'UTF-8') : $subject;
            $encodedName = function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($fromName, 'UTF-8') : $fromName;
            $headers = 'From: ' . $encodedName . ' <' . $from . ">\r\n";
            $headers .= 'To: <' . $to . ">\r\n";
            $headers .= 'Subject: ' . $encodedSubject . "\r\n";
            if ($htmlBody !== null && trim($htmlBody) !== '') {
                $boundary = '=_MocBan_' . bin2hex(random_bytes(12));
                $headers .= "MIME-Version: 1.0\r\nContent-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";
                $body = preg_replace('/(?m)^\./', '..', $body);
                $htmlBody = preg_replace('/(?m)^\./', '..', $htmlBody);
                $message = '--' . $boundary . "\r\n";
                $message .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n";
                $message .= $body . "\r\n\r\n--" . $boundary . "\r\n";
                $message .= "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n";
                $message .= $htmlBody . "\r\n\r\n--" . $boundary . "--\r\n";
            } else {
                $headers .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n";
                $message = preg_replace('/(?m)^\./', '..', $body);
            }
            fwrite($this->connection, $headers . $message . "\r\n.\r\n");
            $this->expect(250);
            $this->command('QUIT', 221);
            return true;
        } finally {
            fclose($this->connection);
            $this->connection = null;
        }
    }

    private function command(string $command, int $expected): void
    {
        fwrite($this->connection, $command . "\r\n");
        $this->expect($expected);
    }

    private function expect(int $expected): string
    {
        $response = '';
        do {
            $line = fgets($this->connection, 515);
            if ($line === false) throw new RuntimeException('SMTP không trả về phản hồi.');
            $response .= $line;
        } while (isset($line[3]) && $line[3] === '-');
        $code = (int)substr($response, 0, 3);
        if ($code !== $expected) throw new RuntimeException('SMTP lỗi ' . $code . ': ' . trim($response));
        return $response;
    }
}
