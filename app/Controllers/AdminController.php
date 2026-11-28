<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

abstract class AdminController extends Controller
{
    protected function authorize(): void
    {
        Session::start();
        $user = Session::get('user');

        $isAdmin = is_array($user) && isset($user['role']) && $user['role'] === 'admin';
        if (!$isAdmin) {
            http_response_code(403);
            $this->view->render('errors/error', array(
                'title' => 'Không có quyền truy cập',
                'errorCode' => '403',
                'errorTitle' => 'Bạn không có quyền truy cập',
                'errorMessage' => 'Tài khoản hiện tại không được phép mở khu vực quản trị.',
                'errorLabel' => 'Hãy đăng nhập bằng tài khoản quản trị hoặc quay lại cửa hàng.',
                'primaryUrl' => '/',
                'primaryLabel' => 'Về cửa hàng',
            ));
            exit;
        }
    }
}
