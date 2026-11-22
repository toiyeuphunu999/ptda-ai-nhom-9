<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\MemberPoint;
use App\Models\User;
use App\Models\UserAddress;
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

    public function updateProfile(): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) { header('Location: /login'); return; }
        $name = trim((string)($_POST['name'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $address = trim((string)($_POST['address'] ?? ''));
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            (new User($this->database()))->updateProfile((int)$user['id'], $name, $phone, $address);
            $user['name'] = $name;
            Session::put('user', $user);
            Session::flash('success', 'Đã cập nhật thông tin nhận hàng mặc định.');
            header('Location: /account');
        } catch (Throwable $exception) {
            $this->renderAccount($user, $exception->getMessage(), $name, $phone, $address);
        }
    }

    private function renderAccount(array $user, ?string $error = null, ?string $formName = null, ?string $formPhone = null, ?string $formAddress = null): void
    {
        $database = $this->database();
        $account = (new User($database))->findById((int)$user['id']) ?: $user;
        $points = new MemberPoint($database);
        $this->view->render('shop/account', ['title'=>'Tài khoản','user'=>$account,'addresses'=>(new UserAddress($database))->allForUser((int)$user['id']),'pointsBalance'=>$points->balance((int)$user['id']),'pointsHistory'=>$points->history((int)$user['id']),'profileError'=>$error,'formName'=>$formName ?? $account['name'] ?? '', 'formPhone'=>$formPhone ?? $account['phone'] ?? '', 'formAddress'=>$formAddress ?? $account['address'] ?? '']);
    }

    public function addressStore(): void { $this->addressAction('create'); }
    public function addressUpdate(string $id): void { $this->addressAction('update', (int)$id); }
    public function addressDelete(string $id): void { $this->addressAction('delete', (int)$id); }
    public function addressDefault(string $id): void { $this->addressAction('default', (int)$id); }

    private function addressAction(string $action, int $id = 0): void
    {
        Session::start(); $user = Session::get('user');
        if (!is_array($user)) { header('Location: /login'); return; }
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $addresses = new UserAddress($this->database()); $userId = (int)$user['id'];
            if ($action === 'create') $addresses->create($userId, (string)($_POST['label'] ?? ''), (string)($_POST['recipient_name'] ?? ''), (string)($_POST['phone'] ?? ''), (string)($_POST['address'] ?? ''), isset($_POST['is_default']));
            elseif ($action === 'update') $addresses->update($id, $userId, (string)($_POST['label'] ?? ''), (string)($_POST['recipient_name'] ?? ''), (string)($_POST['phone'] ?? ''), (string)($_POST['address'] ?? ''));
            elseif ($action === 'default') $addresses->setDefault($id, $userId);
            else $addresses->delete($id, $userId);
            Session::flash('success', $action === 'delete' ? 'Đã xóa địa chỉ nhận hàng.' : ($action === 'default' ? 'Đã chọn địa chỉ mặc định.' : 'Đã lưu địa chỉ nhận hàng.'));
        } catch (Throwable $exception) { Session::flash('failure', $exception->getMessage()); }
        header('Location: /account');
    }
    public function orders(): void { header('Location: /orders'); }
}
