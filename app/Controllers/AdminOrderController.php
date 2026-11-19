<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Models\Order;
use App\Services\OrderNotificationService;
use Throwable;

final class AdminOrderController extends AdminController
{
    // Danh sách đơn hàng
    public function orders(): void
    {
        $this->authorize();

        $this->view->render(
            'admin/orders',
            [
                'title' => 'Đơn hàng',
                'orders' => (new Order($this->database()))
                    ->allForAdmin()
            ],
            'admin'
        );
    }

    // Quản lý thanh toán
    public function payments(): void
    {
        $this->authorize();

        $method = trim(
            (string)($_GET['method'] ?? '')
        ) ?: null;

        $status = trim(
            (string)($_GET['status'] ?? '')
        ) ?: null;

        $this->view->render(
            'admin/payments',
            [
                'title' => 'Quản lý thanh toán',
                'payments' => (new Order($this->database()))
                    ->paymentsForAdmin($method, $status),
                'method' => $method,
                'status' => $status
            ],
            'admin'
        );
    }

    // Cập nhật trạng thái đơn hàng
    public function orderStatus(string $id): void
    {
        $this->authorize();

        try {

            Csrf::verify($_POST['_csrf'] ?? null);

            $newStatus = (string)(
                $_POST['status'] ?? ''
            );

            $database = $this->database();

            // Lấy trạng thái hiện tại
            $statement = $database->connection()->prepare(
                'SELECT status
                 FROM orders
                 WHERE id = :id
                 LIMIT 1'
            );

            $statement->execute([
                'id' => (int)$id
            ]);

            $oldStatus = $statement->fetchColumn();

            if ($oldStatus === false) {
                throw new \RuntimeException(
                    'Không tìm thấy đơn hàng.'
                );
            }

            // Cập nhật đơn hàng
            $user = Session::get('user');

            (new Order($database))->updateStatus(
                (int)$id,
                $newStatus,
                is_array($user)
                    ? (int)$user['id']
                    : null
            );

            // Chỉ gửi thông báo khi trạng thái thực sự thay đổi
            if ($oldStatus !== $newStatus) {

                (new OrderNotificationService($database))
                    ->statusChanged(
                        (int)$id,
                        $newStatus
                    );
            }

            Session::flash(
                'success',
                'Đã cập nhật trạng thái đơn hàng.'
            );

            header('Location: /admin/orders');

        } catch (Throwable $exception) {

            http_response_code(422);

            $this->view->render(
                'admin/orders',
                [
                    'title' => 'Đơn hàng',
                    'error' => $exception->getMessage(),
                    'orders' => (new Order($this->database()))
                        ->allForAdmin()
                ],
                'admin'
            );
        }
    }
}