<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Csrf;
use App\Models\MemberPoint;
use App\Models\User;
use App\Models\Notification;
use Throwable;

final class AccountPageController extends Controller
{
    // Trang tài khoản
    public function account(): void
    {
        Session::start();

        $user = Session::get('user');

        if (!is_array($user)) {
            header('Location: /login');
            return;
        }

        $this->renderAccount($user);
    }

    // Cập nhật tên
    public function updateName(): void
    {
        Session::start();

        $user = Session::get('user');

        if (!is_array($user)) {
            header('Location: /login');
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));

        try {
            Csrf::verify($_POST['_csrf'] ?? null);

            (new User($this->database()))
                ->updateName((int)$user['id'], $name);

            $user['name'] = $name;

            Session::put('user', $user);

            Session::flash(
                'success',
                'Đã cập nhật họ tên tài khoản.'
            );

            header('Location: /account');

        } catch (Throwable $exception) {

            $this->renderAccount(
                $user,
                $exception->getMessage(),
                $name
            );
        }
    }

    // Đánh dấu một thông báo đã đọc
    public function markNotificationRead(string $id): void
    {
        Session::start();

        $user = Session::get('user');

        if (!is_array($user)) {
            header('Location: /login');
            return;
        }

        try {
            Csrf::verify($_POST['_csrf'] ?? null);

            (new Notification($this->database()))
                ->markRead(
                    (int)$id,
                    (int)$user['id']
                );

        } catch (Throwable $exception) {

            Session::flash(
                'failure',
                'Không thể cập nhật thông báo.'
            );
        }

        header('Location: /account#notifications');
    }

    // Đánh dấu tất cả thông báo đã đọc
    public function markAllNotificationsRead(): void
    {
        Session::start();

        $user = Session::get('user');

        if (!is_array($user)) {
            header('Location: /login');
            return;
        }

        try {
            Csrf::verify($_POST['_csrf'] ?? null);

            (new Notification($this->database()))
                ->markAllRead((int)$user['id']);

        } catch (Throwable $exception) {

            Session::flash(
                'failure',
                'Không thể cập nhật thông báo.'
            );
        }

        header('Location: /account#notifications');
    }

    // Hiển thị tài khoản
    private function renderAccount(
        array $user,
        ?string $error = null,
        ?string $formName = null
    ): void {

        $database = $this->database();

        $account = (new User($database))
            ->findById((int)$user['id']) ?: $user;

        $points = new MemberPoint($database);

        $notificationModel = new Notification($database);

        $notifications = $notificationModel->forUser(
            (int)$user['id']
        );

        $unreadCount = $notificationModel->unreadCount(
            (int)$user['id']
        );

        $this->view->render('shop/account', [

            'title' => 'Tài khoản',

            'user' => $account,

            'pointsBalance' => $points->balance(
                (int)$user['id']
            ),

            'pointsHistory' => $points->history(
                (int)$user['id']
            ),

            'notifications' => $notifications,

            'unreadCount' => $unreadCount,

            'profileError' => $error,

            'formName' => $formName
                ?? $account['name']
                ?? ''

        ]);
    }

    public function orders(): void
    {
        header('Location: /orders');
    }
}