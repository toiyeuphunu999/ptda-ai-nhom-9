<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\SystemSetting;
use Throwable;
final class AdminSettingsController extends AdminController
{
    public function settings(): void { $this->authorize(); $this->renderSettings(); }
    public function save(): void
    {
        $this->authorize(); try { Csrf::verify($_POST['_csrf']??null); $user=Session::get('user'); $settings=new SystemSetting($this->database()); $settings->saveMany($settings->dataFromInput($_POST),(int)$user['id']); Session::flash('success','Đã lưu cài đặt hệ thống.'); header('Location: /admin/settings'); } catch(Throwable $e) { http_response_code(422); $this->renderSettings($e->getMessage()); }
    }
    private function renderSettings(?string $error=null): void { $settings=new SystemSetting($this->database()); $this->view->render('admin/settings',$settings->adminViewData($error),'admin'); }
}
