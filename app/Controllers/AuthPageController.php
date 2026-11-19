<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\EmailTemplate;
use App\Core\Session;
use App\Core\SmtpMailer;
use App\Models\User;
use App\Models\MemberPoint;
use App\Models\SystemSetting;
use Throwable;

final class AuthPageController extends Controller
{
    private function users(): User { return new User($this->database()); }
    public function login(): void { Session::start(); $this->view->render('shop/login', ['title' => 'Đăng nhập']); }
    public function register(): void { Session::start(); $this->view->render('shop/register', ['title' => 'Đăng ký']); }

    public function forgotPassword(): void
    {
        Session::start();
        $this->view->render('shop/forgot_password', ['title' => 'Quên mật khẩu']);
    }

    public function forgotPasswordSubmit(): void
    {
        Session::start();
        $email = trim((string)($_POST['email'] ?? ''));
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Vui lòng nhập email hợp lệ.');
            }

            $account = $this->users()->findByEmail($email);
            if ($account) {
                $token = $this->users()->createPasswordResetToken((int)$account['id']);
                $this->sendPasswordResetEmail($account['email'], $account['name'], $token);
            }
            Session::flash('success', 'Nếu email tồn tại, hướng dẫn đặt lại mật khẩu đã được gửi. Hãy kiểm tra cả mục Spam.');
            header('Location: /login');
        } catch (Throwable $exception) {
            $this->view->render('shop/forgot_password', ['title' => 'Quên mật khẩu', 'error' => $exception->getMessage(), 'email' => $email]);
        }
    }

    public function resetPassword(): void
    {
        Session::start();
        $token = trim((string)($_GET['token'] ?? ''));
        if ($token === '') {
            Session::flash('failure', 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
            header('Location: /forgot-password');
            return;
        }
        $this->view->render('shop/reset_password', ['title' => 'Đặt lại mật khẩu', 'token' => $token]);
    }

    public function resetPasswordSubmit(): void
    {
        Session::start();
        $token = trim((string)($_POST['token'] ?? ''));
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $password = (string)($_POST['password'] ?? '');
            $confirmation = (string)($_POST['password_confirmation'] ?? '');
            if ($token === '') throw new \RuntimeException('Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
            if (strlen($password) < 8) throw new \RuntimeException('Mật khẩu mới phải có ít nhất 8 ký tự.');
            if ($password !== $confirmation) throw new \RuntimeException('Mật khẩu xác nhận không khớp.');
            if (!$this->users()->resetPassword($token, $password)) {
                throw new \RuntimeException('Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
            }
            Session::flash('success', 'Đặt lại mật khẩu thành công. Bạn có thể đăng nhập bằng mật khẩu mới.');
            header('Location: /login');
        } catch (Throwable $exception) {
            $this->view->render('shop/reset_password', ['title' => 'Đặt lại mật khẩu', 'error' => $exception->getMessage(), 'token' => $token]);
        }
    }

    public function googleRedirect(): void
    {
        Session::start();
        $config = $this->googleConfig();
        if ($config['client_id'] === '' || $config['client_secret'] === '') {
            $this->view->render('shop/login', ['title'=>'Đăng nhập', 'error'=>'Đăng nhập bằng Google chưa được cấu hình.']);
            return;
        }
        $state = bin2hex(random_bytes(32));
        Session::put('google_oauth_state', $state);
        $query = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);
        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function googleCallback(): void
    {
        Session::start();
        try {
            $state = (string)($_GET['state'] ?? '');
            $savedState = (string)Session::get('google_oauth_state', '');
            Session::forget('google_oauth_state');
            if ($state === '' || $savedState === '' || !hash_equals($savedState, $state)) {
                throw new \RuntimeException('Phiên đăng nhập Google không hợp lệ.');
            }
            if (!empty($_GET['error'])) throw new \RuntimeException('Bạn đã hủy đăng nhập bằng Google.');
            $code = trim((string)($_GET['code'] ?? ''));
            if ($code === '') throw new \RuntimeException('Google không trả về mã đăng nhập.');
            $config = $this->googleConfig();
            $token = $this->googleRequest('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri' => $config['redirect_uri'],
                'grant_type' => 'authorization_code',
            ]);
            if (empty($token['access_token'])) throw new \RuntimeException('Không lấy được token từ Google.');
            $profile = $this->googleProfile((string)$token['access_token']);
            if (empty($profile['sub']) || empty($profile['email']) || ($profile['email_verified'] ?? false) !== true) {
                throw new \RuntimeException('Tài khoản Google chưa cung cấp email đã xác thực.');
            }
            $users = $this->users();
            $account = $users->findByGoogleId((string)$profile['sub']);
            if (!$account) {
                $account = $users->findByEmail((string)$profile['email']);
                if ($account) {
                    if (!empty($account['google_id']) && $account['google_id'] !== (string)$profile['sub']) throw new \RuntimeException('Email này đã liên kết với tài khoản Google khác.');
                    $users->linkGoogleId((int)$account['id'], (string)$profile['sub']);
                } else {
                    $id = $users->createFromGoogle((string)($profile['name'] ?? 'Google User'), (string)$profile['email'], (string)$profile['sub']);
                    $account = $users->findById($id);
                }
            }
            Session::regenerate();
            Session::put('user', ['id'=>(int)$account['id'], 'name'=>$account['name'], 'role'=>$account['role']]);
            header('Location: ' . ($account['role'] === 'admin' ? '/admin' : '/account'));
        } catch (Throwable $exception) {
            $this->view->render('shop/login', ['title'=>'Đăng nhập', 'error'=>$exception->getMessage()]);
        }
    }

    public function loginSubmit(): void
    {
        Session::start(); $email = trim((string)($_POST['email'] ?? ''));
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $account = filter_var($email, FILTER_VALIDATE_EMAIL) ? $this->users()->findByEmail($email) : null;
            if (!$account || !password_verify((string)($_POST['password'] ?? ''), $account['password_hash'])) throw new \RuntimeException('Email hoặc mật khẩu không đúng.');
            if (empty($account['email_verified_at'])) throw new \RuntimeException('Vui lòng xác thực email trước khi đăng nhập.');
            $user = ['id'=>(int)$account['id'], 'name'=>$account['name'], 'role'=>$account['role']];
            Session::regenerate(); Session::put('user', $user);
            header('Location: ' . ($user['role'] === 'admin' ? '/admin' : '/account'));
        } catch (Throwable $exception) { $this->view->render('shop/login', ['title' => 'Đăng nhập', 'error' => $exception->getMessage(), 'email' => $email]); }
    }

    public function registerSubmit(): void
    {
        Session::start(); $name = trim((string)($_POST['name'] ?? '')); $email = trim((string)($_POST['email'] ?? ''));
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $password = (string)($_POST['password'] ?? '');
            $referralCode = trim((string)($_POST['referral_code'] ?? ''));
            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) throw new \RuntimeException('Vui lòng nhập tên, email hợp lệ và mật khẩu tối thiểu 8 ký tự.');
            if ($this->users()->findByEmail($email)) throw new \RuntimeException('Email này đã được đăng ký.');
            if ($referralCode !== '' && !$this->users()->findByReferralCode($referralCode)) throw new \RuntimeException('Mã giới thiệu không tồn tại.');
            $id = $this->users()->create($name, $email, $password, $referralCode);
            (new MemberPoint($this->database()))->earnForReferral($id);
            $account = $this->users()->findById($id);
            $token = $this->users()->createVerificationToken($id);
            if ($this->sendVerificationEmail($account['email'], $account['name'], $token)) {
                Session::flash('success', 'Đăng ký thành công. Hãy kiểm tra email để xác thực tài khoản.');
            } else {
                Session::flash('failure', 'Tài khoản đã tạo nhưng hệ thống chưa gửi được email xác thực. Hãy kiểm tra cấu hình mail.');
            }
            header('Location: /login');
        } catch (Throwable $exception) { $this->view->render('shop/register', ['title' => 'Đăng ký', 'error' => $exception->getMessage(), 'name' => $name, 'email' => $email]); }
    }

    public function verifyEmail(): void
    {
        Session::start();
        $token = trim((string)($_GET['token'] ?? ''));
        if ($token !== '' && (new User($this->database()))->verifyEmail($token)) {
            Session::flash('success', 'Email đã được xác thực. Bạn có thể đăng nhập.');
        } else {
            Session::flash('failure', 'Link xác thực không hợp lệ hoặc đã hết hạn.');
        }
        header('Location: /login');
    }

    public function resendVerification(): void
    {
        Session::start();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $email = trim((string)($_POST['verification_email'] ?? ''));
            $account = filter_var($email, FILTER_VALIDATE_EMAIL) ? $this->users()->findByEmail($email) : null;
            if ($account && empty($account['email_verified_at'])) {
                $token = $this->users()->createVerificationToken((int)$account['id']);
                $this->sendVerificationEmail($account['email'], $account['name'], $token);
            }
            Session::flash('success', 'Nếu email tồn tại và chưa xác thực, link mới đã được gửi.');
        } catch (Throwable $exception) {
            Session::flash('failure', $exception->getMessage());
        }
        header('Location: /login');
    }

    public function logout(): void
    {
        Session::start(); Csrf::verify($_POST['_csrf'] ?? null); Session::regenerate(); $_SESSION = []; header('Location: /');
    }

    private function googleConfig(): array
    {
        return (new SystemSetting($this->database()))->googleConfig();
    }

    private function sendVerificationEmail(string $email, string $name, string $token): bool
    {
        $mail = (new SystemSetting($this->database()))->mailConfig();
        $baseUrl = rtrim((string)(getenv('APP_URL') ?: $this->currentHttpHost()), '/');
        $link = $baseUrl . '/verify-email?token=' . rawurlencode($token);
        $subject = 'Xác thực email - Nhà sách Mộc Bản';
        $message = "Xin chào {$name},\n\nHãy mở liên kết sau để xác thực tài khoản:\n{$link}\n\nLiên kết có hiệu lực trong 24 giờ.";
        return (new SmtpMailer($mail))->send($email, $subject, $message, EmailTemplate::verification($name, $link));
    }

    private function sendPasswordResetEmail(string $email, string $name, string $token): bool
    {
        $mail = (new SystemSetting($this->database()))->mailConfig();
        $baseUrl = rtrim((string)(getenv('APP_URL') ?: $this->currentHttpHost()), '/');
        $link = $baseUrl . '/reset-password?token=' . rawurlencode($token);
        $subject = 'Đặt lại mật khẩu - Nhà sách Mộc Bản';
        $message = "Xin chào {$name},\n\nHãy mở liên kết sau để đặt lại mật khẩu:\n{$link}\n\nLiên kết có hiệu lực trong 60 phút và chỉ sử dụng được một lần. Nếu bạn không yêu cầu, hãy bỏ qua email này.";
        return (new SmtpMailer($mail))->send($email, $subject, $message, EmailTemplate::passwordReset($name, $link));
    }

    private function currentHttpHost(): string
    {
        $host = preg_replace('/[^a-zA-Z0-9.:\-\[\]]/', '', (string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
        return 'http://' . ($host !== '' ? $host : 'localhost');
    }

    private function googleRequest(string $url, array $fields): array
    {
        $handle = curl_init($url);
        if ($handle === false) throw new \RuntimeException('Không khởi tạo được kết nối Google.');
        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        $body = curl_exec($handle); $error = curl_error($handle); curl_close($handle);
        if ($body === false || $error !== '') throw new \RuntimeException('Không thể kết nối Google.');
        $data = json_decode((string)$body, true);
        if (!is_array($data)) throw new \RuntimeException('Google trả về dữ liệu không hợp lệ.');
        return $data;
    }

    private function googleProfile(string $accessToken): array
    {
        $handle = curl_init('https://openidconnect.googleapis.com/v1/userinfo');
        if ($handle === false) throw new \RuntimeException('Không khởi tạo được kết nối Google.');
        curl_setopt_array($handle, [CURLOPT_HTTPHEADER=>['Authorization: Bearer ' . $accessToken], CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>15]);
        $body = curl_exec($handle); $error = curl_error($handle); curl_close($handle);
        if ($body === false || $error !== '') throw new \RuntimeException('Không thể lấy thông tin tài khoản Google.');
        $profile = json_decode((string)$body, true);
        if (!is_array($profile)) throw new \RuntimeException('Thông tin tài khoản Google không hợp lệ.');
        return $profile;
    }
}
