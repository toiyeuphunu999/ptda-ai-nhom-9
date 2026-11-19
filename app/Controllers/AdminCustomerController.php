<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\User;
use Throwable;
final class AdminCustomerController extends AdminController
{
    private function renderDetails(int $id, ?string $error = null): void
    {
        $user = new User($this->database());
        $customer = $user->adminDetails($id);
        if (!$customer) { http_response_code(404); $this->view->render('errors/error', ['title'=>'Không tìm thấy khách hàng','errorCode'=>'404','errorTitle'=>'Không tìm thấy khách hàng','errorMessage'=>'Tài khoản này không tồn tại hoặc đã bị xóa.','errorLabel'=>'Quay lại danh sách khách hàng để tiếp tục.','primaryUrl'=>'/admin/customers','primaryLabel'=>'Danh sách khách hàng']); return; }
        $this->view->render('admin/customer_detail', ['title'=>'Quản lý khách hàng','customer'=>$customer,'walletHistory'=>$user->walletHistory($id),'error'=>$error], 'admin');
    }

    public function customers(): void { $this->authorize(); $this->view->render('admin/customers',['title'=>'Khách hàng','customers'=>(new User($this->database()))->allForAdmin()],'admin'); }
    public function detail(string $id): void { $this->authorize(); $this->renderDetails((int)$id); }

    public function updateProfile(string $id): void
    {
        $this->authorize();
        try { Csrf::verify($_POST['_csrf'] ?? null); (new User($this->database()))->updateByAdmin((int)$id, (string)($_POST['name'] ?? ''), (string)($_POST['email'] ?? ''), (string)($_POST['phone'] ?? ''), (string)($_POST['address'] ?? '')); Session::flash('success', 'Đã cập nhật thông tin khách hàng.'); header('Location: /admin/customers/'.$id); }
        catch (Throwable $exception) { http_response_code(422); $this->renderDetails((int)$id, $exception->getMessage()); }
    }

    public function updatePurchaseStatus(string $id): void
    {
        $this->authorize();
        try { Csrf::verify($_POST['_csrf'] ?? null); $blocked = (string)($_POST['purchase_status'] ?? '') === 'blocked'; (new User($this->database()))->setPurchaseBlocked((int)$id, $blocked); Session::flash('success', $blocked ? 'Đã khóa quyền mua của khách hàng.' : 'Đã mở lại quyền mua của khách hàng.'); header('Location: /admin/customers/'.$id); }
        catch (Throwable $exception) { http_response_code(422); $this->renderDetails((int)$id, $exception->getMessage()); }
    }

    public function adjustWallet(string $id): void
    {
        $this->authorize();
        try { Csrf::verify($_POST['_csrf'] ?? null); $admin = Session::get('user'); (new User($this->database()))->adjustWallet((int)$id, (int)($_POST['amount'] ?? 0), (string)($_POST['description'] ?? ''), is_array($admin) ? (int)$admin['id'] : null); Session::flash('success', 'Đã cập nhật số dư ví và lưu lịch sử giao dịch.'); header('Location: /admin/customers/'.$id); }
        catch (Throwable $exception) { http_response_code(422); $this->renderDetails((int)$id, $exception->getMessage()); }
    }
}
