<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Auth;
use Throwable;

class AuthPageController extends Controller
{
    private function getAuthModel(): Auth
    {
        return new Auth($this->database());
    }

    public function login(): void
    {
        Session::start();
        $this->view->render('shop/login', array('title' => 'Đăng nhập'));
    }

    public function register(): void
    {
        Session::start();
        $this->view->render('shop/register', array('title' => 'Đăng ký'));
    }

    public function forgotPassword(): void
    {
        Session::start();
        $this->view->render('shop/forgot_password', array('title' => 'Quên mật khẩu'));
    }

    public function forgotPasswordSubmit(): void
    {
        Session::start();
        $email = trim((string)($_POST['email'] ?? ''));

        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $this->getAuthModel()->forgotPassword($email);
            Session::flash('success', 'Nếu email tồn tại, hướng dẫn đặt lại mật khẩu đã được gửi.');
            header('Location: /login');
        } catch (Throwable $exception) {
            $data = array('title' => 'Quên mật khẩu');
            $data['error'] = $exception->getMessage();
            $data['email'] = $email;
            $this->view->render('shop/forgot_password', $data);
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
        $this->view->render('shop/reset_password', array('title' => 'Đặt lại mật khẩu', 'token' => $token));
    }

    public function resetPasswordSubmit(): void
    {
        Session::start();
        $token = trim((string)($_POST['token'] ?? ''));

        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $password = (string)($_POST['password'] ?? '');
            $confirmation = (string)($_POST['password_confirmation'] ?? '');
            $this->getAuthModel()->resetPassword($token, $password, $confirmation);
            Session::flash('success', 'Đặt lại mật khẩu thành công.');
            header('Location: /login');
        } catch (Throwable $exception) {
            $data = array('title' => 'Đặt lại mật khẩu');
            $data['error'] = $exception->getMessage();
            $data['token'] = $token;
            $this->view->render('shop/reset_password', $data);
        }
    }

    public function googleRedirect(): void
    {
        Session::start();

        try {
            $state = bin2hex(random_bytes(32));
            Session::put('google_oauth_state', $state);
            $url = $this->getAuthModel()->googleAuthorizationUrl($state);
            header('Location: ' . $url);
        } catch (Throwable $exception) {
            $this->view->render('shop/login', array(
                'title' => 'Đăng nhập',
                'error' => $exception->getMessage(),
            ));
        }
    }

    public function googleCallback(): void
    {
        Session::start();
        $state = (string)($_GET['state'] ?? '');
        $savedState = (string)Session::get('google_oauth_state', '');
        Session::forget('google_oauth_state');

        try {
            if (!empty($_GET['error'])) {
                throw new \RuntimeException('Bạn đã hủy đăng nhập bằng Google.');
            }
            $code = trim((string)($_GET['code'] ?? ''));
            $account = $this->getAuthModel()->googleAuthenticate($code, $state, $savedState);
            $this->saveLoginSession($account);
            if ($account['role'] === 'admin') {
                header('Location: /admin');
            } else {
                header('Location: /account');
            }
        } catch (Throwable $exception) {
            $this->view->render('shop/login', array(
                'title' => 'Đăng nhập',
                'error' => $exception->getMessage(),
            ));
        }
    }

    public function loginSubmit(): void
    {
        Session::start();
        $email = trim((string)($_POST['email'] ?? ''));

        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $account = $this->getAuthModel()->authenticate($email, (string)($_POST['password'] ?? ''));
            $this->saveLoginSession($account);
            if ($account['role'] === 'admin') {
                header('Location: /admin');
            } else {
                header('Location: /account');
            }
        } catch (Throwable $exception) {
            $this->view->render('shop/login', array(
                'title' => 'Đăng nhập',
                'error' => $exception->getMessage(),
                'email' => $email,
            ));
        }
    }

    public function registerSubmit(): void
    {
        Session::start();
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));

        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $result = $this->getAuthModel()->register(
                $name,
                $email,
                (string)($_POST['password'] ?? ''),
                trim((string)($_POST['referral_code'] ?? ''))
            );
            if ($result['emailSent']) {
                Session::flash('success', 'Đăng ký thành công. Hãy kiểm tra email để xác thực tài khoản.');
            } else {
                Session::flash('failure', 'Tài khoản đã tạo nhưng chưa gửi được email xác thực.');
            }
            header('Location: /login');
        } catch (Throwable $exception) {
            $data = array('title' => 'Đăng ký');
            $data['error'] = $exception->getMessage();
            $data['name'] = $name;
            $data['email'] = $email;
            $this->view->render('shop/register', $data);
        }
    }

    public function verifyEmail(): void
    {
        Session::start();
        $verified = $this->getAuthModel()->verifyEmail((string)($_GET['token'] ?? ''));
        if ($verified) {
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
            $email = (string)($_POST['verification_email'] ?? '');
            $this->getAuthModel()->resendVerification($email);
            Session::flash('success', 'Nếu email tồn tại và chưa xác thực, link mới đã được gửi.');
        } catch (Throwable $exception) {
            Session::flash('failure', $exception->getMessage());
        }
        header('Location: /login');
    }

    public function logout(): void
    {
        Session::start();
        Csrf::verify($_POST['_csrf'] ?? null);
        Session::regenerate();
        $_SESSION = array();
        header('Location: /');
    }

    private function saveLoginSession(array $account): void
    {
        Session::regenerate();
        $user = array();
        $user['id'] = (int)$account['id'];
        $user['name'] = $account['name'];
        $user['role'] = $account['role'];
        Session::put('user', $user);
    }
}
