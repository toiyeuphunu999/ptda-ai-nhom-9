<?php
use App\Core\Csrf;
use App\Helpers\ViewHelper;

$settings = $settings ?? [];
$settingMap = [];
foreach ($settings as $setting) $settingMap[$setting['setting_key']] = $setting;
$value = function (string $key, string $fallback = '') use ($settingMap): string { return ViewHelper::escape((string)($settingMap[$key]['setting_value'] ?? $fallback)); };
$payosConfig = $payosConfig ?? [];
$payosValue = function (string $key, string $fallback = '') use ($payosConfig): string { return ViewHelper::escape((string)($payosConfig[$key] ?? $fallback)); };
$configured = (bool)($payosConfigured ?? false);
?>
<div class="section-heading report-heading">
    <div><p class="eyebrow">Vận hành</p><h2>Cài đặt hệ thống</h2><p class="muted">Quản lý thông tin cửa hàng và kết nối thanh toán.</p></div>
    <a class="button" href="/admin">Về tổng quan</a>
</div>
<?php if (!empty($error)): ?><div class="notice"><?= ViewHelper::escape($error) ?></div><?php endif; ?>
<form class="settings-form" method="post" action="/admin/settings">
    <?= Csrf::field() ?>
    <section class="admin-panel settings-panel"><div class="section-heading"><div><p class="eyebrow">Cửa hàng</p><h3>Thông tin vận hành</h3></div></div>
        <div class="settings-grid">
            <div class="settings-field"><label for="shop-phone">Số điện thoại hỗ trợ</label><input id="shop-phone" name="shop_phone" type="tel" value="<?= $value('SHOP_PHONE') ?>" placeholder="0901 234 567"></div>
            <div class="settings-field"><label for="shop-email">Email hỗ trợ</label><input id="shop-email" name="shop_email" type="email" value="<?= $value('SHOP_EMAIL') ?>" placeholder="hello@mocban.vn"></div>
            <div class="settings-field"><label for="shipping-fee">Phí vận chuyển mặc định (VNĐ)</label><input id="shipping-fee" name="shipping_fee" type="number" min="0" value="<?= $value('SHIPPING_FEE', '0') ?>"></div>
        </div>
    </section>
    <section class="admin-panel settings-panel"><div class="section-heading"><div><p class="eyebrow">PayOS</p><h3>Thanh toán online</h3></div><span class="report-badge <?= $configured ? 'report-badge-paid' : 'report-badge-neutral' ?>"><?= $configured ? 'Đã cấu hình' : 'Chưa cấu hình' ?></span></div>
        <p class="muted">Các khóa được mã hóa bằng APP_KEY trong cơ sở dữ liệu và hiển thị trực tiếp trong biểu mẫu quản trị. Để trống ô khóa nếu muốn giữ cấu hình hiện tại.</p>
        <div class="settings-grid">
            <div class="settings-field"><label for="payos-client-id">Client ID</label><input id="payos-client-id" name="payos_client_id" type="text" value="<?= $payosValue('client_id') ?>" placeholder="Nhập Client ID PayOS" autocomplete="off"></div>
            <div class="settings-field"><label for="payos-api-key">API Key</label><input id="payos-api-key" name="payos_api_key" type="text" value="<?= $payosValue('api_key') ?>" placeholder="Nhập API Key PayOS" autocomplete="off"></div>
            <div class="settings-field"><label for="payos-checksum-key">Checksum Key</label><input id="payos-checksum-key" name="payos_checksum_key" type="text" value="<?= $payosValue('checksum_key') ?>" placeholder="Nhập Checksum Key PayOS" autocomplete="off"></div>
        </div>
        <p class="notice">Webhook cần trỏ tới: <code>http://<?= ViewHelper::escape((string)($_SERVER['HTTP_HOST'] ?? 'localhost')) ?>/payment/payos/webhook</code></p>
    </section>
    <div class="settings-actions"><button class="button button-primary" type="submit">Lưu cài đặt</button></div>
</form>
