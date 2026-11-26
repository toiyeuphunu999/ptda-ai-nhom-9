<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Voucher;
use Throwable;
final class AdminVoucherController extends AdminController
{
    public function vouchers(): void
    {
        $this->authorize();
        $data = array('title' => 'Mã giảm giá');
        $data['vouchers'] = (new Voucher($this->database()))->allForAdmin();
        $this->view->render('admin/vouchers', $data, 'admin');
    }

    public function store(): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $usageLimit = null;
            if (($_POST['usage_limit'] ?? '') !== '') {
                $usageLimit = (int)$_POST['usage_limit'];
            }
            $voucherModel = new Voucher($this->database());
            $voucherModel->create(
                (string)($_POST['code'] ?? ''),
                (string)($_POST['type'] ?? ''),
                (int)($_POST['value'] ?? 0),
                (int)($_POST['min_order_amount'] ?? 0),
                trim((string)($_POST['starts_at'] ?? '')) ?: null,
                trim((string)($_POST['expires_at'] ?? '')) ?: null,
                $usageLimit
            );
            Session::flash('success', 'Đã tạo mã giảm giá thành công.');
            header('Location: /admin/vouchers');
        } catch (Throwable $exception) {
            http_response_code(422);
            $data = array('title' => 'Mã giảm giá');
            $data['error'] = $exception->getMessage();
            $voucherModel = new Voucher($this->database());
            $data['vouchers'] = $voucherModel->allForAdmin();
            $this->view->render('admin/vouchers', $data, 'admin');
        }
    }

    public function delete(string $id): void
    {
        $this->authorize();
        Csrf::verify($_POST['_csrf'] ?? null);
        (new Voucher($this->database()))->delete((int)$id);
        Session::flash('success', 'Đã xóa mã giảm giá thành công.');
        header('Location: /admin/vouchers');
    }
}
