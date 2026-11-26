<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\SystemSetting;
use Throwable;
final class AdminSettingsController extends AdminController
{
    public function settings(): void
    {
        $this->authorize();
        $this->renderSettings();
    }

    public function save(): void
    {
        $this->authorize();

        try {
            Csrf::verify($_POST['_csrf'] ?? null);

            $currentUser = Session::get('user');
            $settingsModel = new SystemSetting($this->database());
            $settings = $settingsModel->dataFromInput($_POST);

            $settingsModel->saveMany($settings, (int)$currentUser['id']);

            Session::flash('success', 'Đã lưu cài đặt hệ thống.');
            header('Location: /admin/settings');
        } catch (Throwable $exception) {
            http_response_code(422);
            $this->renderSettings($exception->getMessage());
        }
    }

    private function renderSettings(?string $error = null): void
    {
        $settingsModel = new SystemSetting($this->database());
        $viewData = $settingsModel->adminViewData($error);
        $this->view->render('admin/settings', $viewData, 'admin');
    }
}
