<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Order;
use Throwable;

final class AdminOrderController extends AdminController
{
    public function orders(): void { $this->authorize(); $this->view->render('admin/orders',['title'=>'Đơn hàng','orders'=>(new Order($this->database()))->allForAdmin()],'admin'); }
    public function payments(): void { $this->authorize(); $method=trim((string)($_GET['method']??''))?:null; $status=trim((string)($_GET['status']??''))?:null; $this->view->render('admin/payments',['title'=>'Quản lý thanh toán','payments'=>(new Order($this->database()))->paymentsForAdmin($method,$status),'method'=>$method,'status'=>$status],'admin'); }
    public function orderStatus(string $id): void
    {
        $this->authorize(); try { Csrf::verify($_POST['_csrf']??null); $user=Session::get('user'); (new Order($this->database()))->updateStatus((int)$id,(string)($_POST['status']??''),is_array($user)?(int)$user['id']:null); Session::flash('success','Đã cập nhật trạng thái đơn hàng.'); header('Location: /admin/orders'); } catch(Throwable $e) { http_response_code(422); $this->view->render('admin/orders',['title'=>'Đơn hàng','error'=>$e->getMessage(),'orders'=>(new Order($this->database()))->allForAdmin()],'admin'); }
    }
}
