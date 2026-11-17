<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;

final class AdminDashboardController extends AdminController
{
    public function dashboard(): void
    {
        $this->authorize(); $db = $this->database(); $orders = new Order($db);
        $this->view->render('admin/dashboard', ['title'=>'Tổng quan','bookCount'=>(new Book($db))->countAll(),'categoryCount'=>(new Category($db))->countAll(),'orderCount'=>$orders->countAll(),'customerCount'=>(new User($db))->countAll(),'metrics'=>$orders->dashboardMetrics()], 'admin');
    }
    public function reports(): void
    {
        $this->authorize(); $this->view->render('admin/reports', ['title'=>'Báo cáo bán hàng','reports'=>(new Order($this->database()))->businessReports()], 'admin');
    }
}
