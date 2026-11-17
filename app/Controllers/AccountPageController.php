<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\MemberPoint;
use App\Models\User;
use App\Core\Csrf;
use Throwable;

final class AccountPageController extends Controller
{
    public function account(): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) { header('Location: /login'); return; }
        $this->renderAccount($user);
    }

    public function updateName(): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) { header('Location: /login'); return; }
        $name = trim((string)($_POST['name'] ?? ''));
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            (new User($this->database()))->updateName((int)$user['id'], $name);
            $user['name'] = $name;
            Session::put('user', $user);
            Session::flash('success', 'Đã cập nhật họ tên tài khoản.');
            header('Location: /account');
        } catch (Throwable $exception) {
            $this->renderAccount($user, $exception->getMessage(), $name);
        }
    }

    private function renderAccount(array $user, ?string $error = null, ?string $formName = null): void
    {
        $database = $this->database();
        $account = (new User($database))->findById((int)$user['id']) ?: $user;
        $points = new MemberPoint($database);
        $this->view->render('shop/account', ['title'=>'Tài khoản','user'=>$account,'pointsBalance'=>$points->balance((int)$user['id']),'pointsHistory'=>$points->history((int)$user['id']),'profileError'=>$error,'formName'=>$formName ?? $account['name'] ?? '']);
    }
    public function orders(): void { header('Location: /orders'); }
}
