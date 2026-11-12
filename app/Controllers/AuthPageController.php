<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Session;
use App\Models\User;
use Throwable;

final class AuthPageController extends Controller
{
    private function users(): User { return new User(new Database(require dirname(__DIR__, 2) . '/config/database.php')); }
    public function login(): void { Session::start(); $this->view->render('shop/login', ['title'=>'Đăng nhập']); }
    public function register(): void { Session::start(); $this->view->render('shop/register', ['title'=>'Đăng ký']); }

    public function loginSubmit(): void
    {
        Session::start();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $email = trim((string)($_POST['email'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $user = filter_var($email, FILTER_VALIDATE_EMAIL) ? $this->users()->findByEmail($email) : null;
            if (!$user || !password_verify($password, $user['password_hash'])) {
                throw new \RuntimeException('Email hoặc mật khẩu không đúng.');
            }
            Session::regenerate();
            Session::put('user', ['id' => (int)$user['id'], 'name' => $user['name'], 'role' => $user['role']]);
            header('Location: ' . ($user['role'] === 'admin' ? '/admin' : '/account'));
        } catch (Throwable $exception) {
            $this->view->render('shop/login', ['title'=>'Đăng nhập', 'error'=>$exception->getMessage(), 'email'=>$email ?? '']);
        }
    }

    public function registerSubmit(): void
    {
        Session::start();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $name = trim((string)($_POST['name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
                throw new \RuntimeException('Vui lòng nhập tên, email hợp lệ và mật khẩu tối thiểu 8 ký tự.');
            }
            if ($this->users()->findByEmail($email)) {
                throw new \RuntimeException('Email này đã được đăng ký.');
            }
            $id = $this->users()->create($name, $email, $password);
            Session::put('user', ['id'=>$id, 'name'=>$name, 'role'=>'customer']);
            header('Location: /account');
        } catch (Throwable $exception) {
            $this->view->render('shop/register', ['title'=>'Đăng ký', 'error'=>$exception->getMessage()]);
        }
    }

    public function logout(): void
    {
        Session::start();
        Csrf::verify($_POST['_csrf'] ?? null);
        Session::regenerate();
        $_SESSION = [];
        header('Location: /');
    }
}
