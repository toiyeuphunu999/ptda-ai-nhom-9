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

    public function addressStore(): void
    {
        $this->addressAction('create');
    }

    public function addressUpdate(string $id): void
    {
        $addressId = (int)$id;
        $this->addressAction('update', $addressId);
    }

    public function addressDelete(string $id): void
    {
        $addressId = (int)$id;
        $this->addressAction('delete', $addressId);
    }

    public function addressDefault(string $id): void
    {
        $addressId = (int)$id;
        $this->addressAction('default', $addressId);
    }

    private function addressAction(string $action, int $id = 0): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) { header('Location: /login'); return; }
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $addressModel = new UserAddress($this->database());
            $userId = (int)$user['id'];
            $label = (string)($_POST['label'] ?? '');
            $recipientName = (string)($_POST['recipient_name'] ?? '');
            $phone = (string)($_POST['phone'] ?? '');
            $address = (string)($_POST['address'] ?? '');

            if ($action === 'create') {
                $addressModel->create($userId, $label, $recipientName, $phone, $address, isset($_POST['is_default']));
            } elseif ($action === 'update') {
                $addressModel->update($id, $userId, $label, $recipientName, $phone, $address);
            } elseif ($action === 'default') {
                $addressModel->setDefault($id, $userId);
            } else {
                $addressModel->delete($id, $userId);
            }

            $message = 'Đã lưu địa chỉ nhận hàng.';
            if ($action === 'delete') $message = 'Đã xóa địa chỉ nhận hàng.';
            if ($action === 'default') $message = 'Đã chọn địa chỉ mặc định.';
            Session::flash('success', $message);
        } catch (Throwable $exception) {
            Session::flash('failure', $exception->getMessage());
        }
        header('Location: /account');
    }
}
