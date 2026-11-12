<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

final class AccountPageController extends Controller
{
    public function account(): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) { header('Location: /login'); return; }
        $this->view->render('shop/account', ['title'=>'Tài khoản','user'=>$user]);
    }
    public function orders(): void { header('Location: /orders'); }
}
