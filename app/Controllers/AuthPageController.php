<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\User;
use App\Models\MemberPoint;
use App\Models\SystemSetting;
use Throwable;

final class AuthPageController extends Controller
{
    private function users(): User { return new User($this->database()); }
    public function login(): void { Session::start(); $this->view->render('shop/login', ['title' => 'Đăng nhập']); }
    public function register(): void { Session::start(); $this->view->render('shop/register', ['title' => 'Đăng ký']); }

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
            Session::put('user', ['id'=>$id, 'name'=>$name, 'role'=>'customer']);
            header('Location: /account');
        } catch (Throwable $exception) { $this->view->render('shop/register', ['title' => 'Đăng ký', 'error' => $exception->getMessage(), 'name' => $name, 'email' => $email]); }
    }

    public function logout(): void
    {
        Session::start(); Csrf::verify($_POST['_csrf'] ?? null); Session::regenerate(); $_SESSION = []; header('Location: /');
    }

    private function googleConfig(): array
    {
        return (new SystemSetting($this->database()))->googleConfig();
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
