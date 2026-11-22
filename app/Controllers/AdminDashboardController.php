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
        $this->authorize();
        $database = $this->database();
        $orderModel = new Order($database);
        $data = array('title' => 'Tổng quan');
        $data['bookCount'] = (new Book($database))->countAll();
        $data['categoryCount'] = (new Category($database))->countAll();
        $data['orderCount'] = $orderModel->countAll();
        $data['customerCount'] = (new User($database))->countAll();
        $data['metrics'] = $orderModel->dashboardMetrics();
        $this->view->render('admin/dashboard', $data, 'admin');
    }
    public function reports(): void
    {
        $this->authorize();
        $data = array('title' => 'Báo cáo bán hàng');
        $data['reports'] = (new Order($this->database()))->businessReports();
        $this->view->render('admin/reports', $data, 'admin');
    }
}
