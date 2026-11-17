<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Models\User;
final class AdminCustomerController extends AdminController
{
    public function customers(): void { $this->authorize(); $this->view->render('admin/customers',['title'=>'Khách hàng','customers'=>(new User($this->database()))->allForAdmin()],'admin'); }
}
