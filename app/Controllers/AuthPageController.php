<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\User;
use App\Models\MemberPoint;
use Throwable;

final class AuthPageController extends Controller
{
    private function users(): User { return new User($this->database()); }
    public function login(): void { Session::start(); $this->view->render('shop/login', ['title' => 'Đăng nhập']); }
    public function register(): void { Session::start(); $this->view->render('shop/register', ['title' => 'Đăng ký']); }

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
}
