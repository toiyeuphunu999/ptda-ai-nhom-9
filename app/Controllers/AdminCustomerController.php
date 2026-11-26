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
        if (!$customer) {
            http_response_code(404);
            $this->view->render('errors/error', [
                'title' => 'Không tìm thấy khách hàng',
                'errorCode' => '404',
                'errorTitle' => 'Không tìm thấy khách hàng',
                'errorMessage' => 'Tài khoản này không tồn tại hoặc đã bị xóa.',
                'errorLabel' => 'Quay lại danh sách khách hàng để tiếp tục.',
                'primaryUrl' => '/admin/customers',
                'primaryLabel' => 'Danh sách khách hàng',
            ]);
            return;
        }

        $walletHistory = $user->walletHistory($id);
        $this->view->render('admin/customer_detail', [
            'title' => 'Quản lý khách hàng',
            'customer' => $customer,
            'walletHistory' => $walletHistory,
            'error' => $error,
        ], 'admin');
    }

    public function customers(): void
    {
        $this->authorize();
        $userModel = new User($this->database());
        $customers = $userModel->allForAdmin();
        $this->view->render('admin/customers', ['title' => 'Khách hàng', 'customers' => $customers], 'admin');
    }

    public function detail(string $id): void
    {
        $this->authorize();
        $customerId = (int)$id;
        $this->renderDetails($customerId);
    }

    public function updateProfile(string $id): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);

            $userModel = new User($this->database());
            $userModel->updateByAdmin(
                (int)$id,
                (string)($_POST['name'] ?? ''),
                (string)($_POST['email'] ?? ''),
                (string)($_POST['phone'] ?? ''),
                (string)($_POST['address'] ?? '')
            );

            Session::flash('success', 'Đã cập nhật thông tin khách hàng.');
            header('Location: /admin/customers/' . (int)$id);
        } catch (Throwable $exception) {
            http_response_code(422);
            $this->renderDetails((int)$id, $exception->getMessage());
        }
    }

    public function updatePurchaseStatus(string $id): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);

            $purchaseStatus = (string)($_POST['purchase_status'] ?? '');
            $isPurchaseBlocked = $purchaseStatus === 'blocked';
            $userModel = new User($this->database());
            $userModel->setPurchaseBlocked((int)$id, $isPurchaseBlocked);

            $message = $isPurchaseBlocked
                ? 'Đã khóa quyền mua của khách hàng.'
                : 'Đã mở lại quyền mua của khách hàng.';
            Session::flash('success', $message);
            header('Location: /admin/customers/' . (int)$id);
        } catch (Throwable $exception) {
            http_response_code(422);
            $this->renderDetails((int)$id, $exception->getMessage());
        }
    }

    public function adjustWallet(string $id): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);

            $adminUser = Session::get('user');
            $adminId = is_array($adminUser) ? (int)$adminUser['id'] : null;
            $amount = (int)($_POST['amount'] ?? 0);
            $description = (string)($_POST['description'] ?? '');

            $userModel = new User($this->database());
            $userModel->adjustWallet((int)$id, $amount, $description, $adminId);

            Session::flash('success', 'Đã cập nhật số dư ví và lưu lịch sử giao dịch.');
            header('Location: /admin/customers/' . (int)$id);
        } catch (Throwable $exception) {
            http_response_code(422);
            $this->renderDetails((int)$id, $exception->getMessage());
        }
    }
}
