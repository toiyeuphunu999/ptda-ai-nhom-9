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
        $this->authorize(); try { Csrf::verify($_POST['_csrf']??null); $user=Session::get('user'); (new SystemSetting($this->database()))->saveMany([
            'SHOP_PHONE'=>['value'=>trim((string)($_POST['shop_phone']??'')),'secret'=>false],
            'SHOP_EMAIL'=>['value'=>trim((string)($_POST['shop_email']??'')),'secret'=>false],
            'SHIPPING_FEE'=>['value'=>(string)max(0,(int)($_POST['shipping_fee']??0)),'secret'=>false],
            'PAYOS_CLIENT_ID'=>['value'=>trim((string)($_POST['payos_client_id']??'')),'secret'=>true],
            'PAYOS_API_KEY'=>['value'=>trim((string)($_POST['payos_api_key']??'')),'secret'=>true],
            'PAYOS_CHECKSUM_KEY'=>['value'=>trim((string)($_POST['payos_checksum_key']??'')),'secret'=>true]
        ],(int)$user['id']); Session::flash('success','Đã lưu cài đặt hệ thống.'); header('Location: /admin/settings'); } catch(Throwable $e) { http_response_code(422); $this->renderSettings($e->getMessage()); }
    }
    private function renderSettings(?string $error=null): void { $settings=new SystemSetting($this->database()); $config=$settings->payOSConfig(); $this->view->render('admin/settings',['title'=>'Cài đặt hệ thống','settings'=>$settings->allForAdmin(),'payosConfig'=>$config,'payosConfigured'=>trim((string)($config['client_id']??''))!==''&&trim((string)($config['api_key']??''))!==''&&trim((string)($config['checksum_key']??'')),'error'=>$error],'admin'); }
}
