<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\EmailTemplate;
use App\Core\SmtpMailer;
use RuntimeException;

final class Auth
{
    public function __construct(private Database $database) {}

    public function authenticate(string $email, string $password): array
    {
        $email = trim($email);
        $account = filter_var($email, FILTER_VALIDATE_EMAIL) ? (new User($this->database))->findByEmail($email) : null;
        if (!$account || !password_verify($password, $account['password_hash'])) throw new RuntimeException('Email hoặc mật khẩu không đúng.');
        if (empty($account['email_verified_at'])) throw new RuntimeException('Vui lòng xác thực email trước khi đăng nhập.');
        return $account;
    }

    public function register(string $name, string $email, string $password, string $referralCode = ''): array
    {
        $name = trim($name); $email = trim($email); $referralCode = trim($referralCode);
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) throw new RuntimeException('Vui lòng nhập tên, email hợp lệ và mật khẩu tối thiểu 8 ký tự.');
        $users = new User($this->database);
        if ($users->findByEmail($email)) throw new RuntimeException('Email này đã được đăng ký.');
        if ($referralCode !== '' && !$users->findByReferralCode($referralCode)) throw new RuntimeException('Mã giới thiệu không tồn tại.');
        $id = $users->create($name, $email, $password, $referralCode);
        (new MemberPoint($this->database))->earnForReferral($id);
        $account = $users->findById($id);
        $token = $users->createVerificationToken($id);
        $emailSent = $this->sendVerificationEmail($account['email'], $account['name'], $token);
        return ['account' => $account, 'emailSent' => $emailSent];
    }

    public function forgotPassword(string $email): void
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Vui lòng nhập email hợp lệ.');
        $users = new User($this->database); $account = $users->findByEmail($email);
        if ($account) $this->sendPasswordResetEmail($account['email'], $account['name'], $users->createPasswordResetToken((int)$account['id']));
    }

    public function resetPassword(string $token, string $password, string $confirmation): void
    {
        if (trim($token) === '') throw new RuntimeException('Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
        if (strlen($password) < 8) throw new RuntimeException('Mật khẩu mới phải có ít nhất 8 ký tự.');
        if ($password !== $confirmation) throw new RuntimeException('Mật khẩu xác nhận không khớp.');
        if (!(new User($this->database))->resetPassword($token, $password)) throw new RuntimeException('Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
    }

    public function verifyEmail(string $token): bool { return trim($token) !== '' && (new User($this->database))->verifyEmail($token); }

    public function resendVerification(string $email): void
    {
        $email = trim($email); $users = new User($this->database);
        $account = filter_var($email, FILTER_VALIDATE_EMAIL) ? $users->findByEmail($email) : null;
        if ($account && empty($account['email_verified_at'])) $this->sendVerificationEmail($account['email'], $account['name'], $users->createVerificationToken((int)$account['id']));
    }

    public function googleAuthorizationUrl(string $state): string
    {
        $config = (new SystemSetting($this->database))->googleConfig();
        if ($config['client_id'] === '' || $config['client_secret'] === '') throw new RuntimeException('Đăng nhập bằng Google chưa được cấu hình.');
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id'=>$config['client_id'], 'redirect_uri'=>$config['redirect_uri'], 'response_type'=>'code',
            'scope'=>'openid email profile', 'state'=>$state, 'access_type'=>'online', 'prompt'=>'select_account',
        ]);
    }

    public function googleAuthenticate(string $code, string $state, string $savedState): array
    {
        if ($state === '' || $savedState === '' || !hash_equals($savedState, $state)) throw new RuntimeException('Phiên đăng nhập Google không hợp lệ.');
        if ($code === '') throw new RuntimeException('Google không trả về mã đăng nhập.');
        $config = (new SystemSetting($this->database))->googleConfig();
        $token = $this->googleRequest('https://oauth2.googleapis.com/token', ['code'=>$code,'client_id'=>$config['client_id'],'client_secret'=>$config['client_secret'],'redirect_uri'=>$config['redirect_uri'],'grant_type'=>'authorization_code']);
        if (empty($token['access_token'])) throw new RuntimeException('Không lấy được token từ Google.');
        $profile = $this->googleProfile((string)$token['access_token']);
        if (empty($profile['sub']) || empty($profile['email']) || ($profile['email_verified'] ?? false) !== true) throw new RuntimeException('Tài khoản Google chưa cung cấp email đã xác thực.');
        $users = new User($this->database); $account = $users->findByGoogleId((string)$profile['sub']);
        if (!$account) {
            $account = $users->findByEmail((string)$profile['email']);
            if ($account) {
                if (!empty($account['google_id']) && $account['google_id'] !== (string)$profile['sub']) throw new RuntimeException('Email này đã liên kết với tài khoản Google khác.');
                $users->linkGoogleId((int)$account['id'], (string)$profile['sub']);
            } else {
                $account = $users->findById($users->createFromGoogle((string)($profile['name'] ?? 'Google User'), (string)$profile['email'], (string)$profile['sub']));
            }
        }
        return $account;
    }

    private function sendVerificationEmail(string $email, string $name, string $token): bool
    {
        $link = $this->baseUrl() . '/verify-email?token=' . rawurlencode($token);
        return (new SmtpMailer((new SystemSetting($this->database))->mailConfig()))->send($email, 'Xác thực email - Nhà sách Mộc Bản', "Xin chào {$name},\n\nHãy mở liên kết sau để xác thực tài khoản:\n{$link}\n\nLiên kết có hiệu lực trong 24 giờ.", EmailTemplate::verification($name, $link));
    }

    private function sendPasswordResetEmail(string $email, string $name, string $token): bool
    {
        $link = $this->baseUrl() . '/reset-password?token=' . rawurlencode($token);
        return (new SmtpMailer((new SystemSetting($this->database))->mailConfig()))->send($email, 'Đặt lại mật khẩu - Nhà sách Mộc Bản', "Xin chào {$name},\n\nHãy mở liên kết sau để đặt lại mật khẩu:\n{$link}\n\nLiên kết có hiệu lực trong 60 phút và chỉ sử dụng được một lần.", EmailTemplate::passwordReset($name, $link));
    }

    private function baseUrl(): string
    {
        $host = preg_replace('/[^a-zA-Z0-9.:\-\[\]]/', '', (string)($_SERVER['HTTP_HOST'] ?? 'localhost')) ?: 'localhost';
        return rtrim((string)(getenv('APP_URL') ?: 'http://' . $host), '/');
    }

    private function googleRequest(string $url, array $fields): array
    {
        $handle = curl_init($url); if ($handle === false) throw new RuntimeException('Không khởi tạo được kết nối Google.');
        curl_setopt_array($handle, [CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($fields),CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15]);
        $body=curl_exec($handle); $error=curl_error($handle); curl_close($handle);
        if ($body === false || $error !== '') throw new RuntimeException('Không thể kết nối Google.');
        $data=json_decode((string)$body,true); if (!is_array($data)) throw new RuntimeException('Google trả về dữ liệu không hợp lệ.'); return $data;
    }

    private function googleProfile(string $accessToken): array
    {
        $handle=curl_init('https://openidconnect.googleapis.com/v1/userinfo'); if ($handle === false) throw new RuntimeException('Không khởi tạo được kết nối Google.');
        curl_setopt_array($handle,[CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$accessToken],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15]);
        $body=curl_exec($handle); $error=curl_error($handle); curl_close($handle);
        if ($body === false || $error !== '') throw new RuntimeException('Không thể lấy thông tin tài khoản Google.');
        $profile=json_decode((string)$body,true); if (!is_array($profile)) throw new RuntimeException('Thông tin tài khoản Google không hợp lệ.'); return $profile;
    }
}
