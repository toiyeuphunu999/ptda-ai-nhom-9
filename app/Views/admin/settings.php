<?php
use App\Core\Csrf;
use App\Helpers\ViewHelper;

$settings = $settings ?? [];
$settingMap = [];
foreach ($settings as $setting) $settingMap[$setting['setting_key']] = $setting;
$value = static fn(string $key, string $fallback = ''): string => ViewHelper::escape((string)($settingMap[$key]['setting_value'] ?? $fallback));
$payosConfig = $payosConfig ?? [];
$payosValue = static fn(string $key, string $fallback = ''): string => ViewHelper::escape((string)($payosConfig[$key] ?? $fallback));
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
        <p class="muted">Khóa bí mật được mã hóa bằng APP_KEY. Các khóa đang được che mặc định; bấm biểu tượng con mắt để xem đầy đủ. Để trống ô khóa nếu muốn giữ cấu hình hiện tại.</p>
        <div class="settings-grid">
            <div class="settings-field"><label for="payos-client-id">Client ID</label><div class="secret-input"><input id="payos-client-id" name="payos_client_id" type="password" value="<?= $payosValue('client_id') ?>" placeholder="Nhập Client ID PayOS"><button class="input-visibility" type="button" data-toggle-password="payos-client-id" onclick="return window.togglePasswordVisibility(event, this)" aria-label="Hiện Client ID"><span class="material-symbols-outlined" aria-hidden="true">visibility</span></button></div></div>
            <div class="settings-field"><label for="payos-api-key">API Key</label><div class="secret-input"><input id="payos-api-key" name="payos_api_key" type="password" value="<?= $payosValue('api_key') ?>" placeholder="Nhập API Key PayOS"><button class="input-visibility" type="button" data-toggle-password="payos-api-key" onclick="return window.togglePasswordVisibility(event, this)" aria-label="Hiện API Key"><span class="material-symbols-outlined" aria-hidden="true">visibility</span></button></div></div>
            <div class="settings-field"><label for="payos-checksum-key">Checksum Key</label><div class="secret-input"><input id="payos-checksum-key" name="payos_checksum_key" type="password" value="<?= $payosValue('checksum_key') ?>" placeholder="Nhập Checksum Key PayOS"><button class="input-visibility" type="button" data-toggle-password="payos-checksum-key" onclick="return window.togglePasswordVisibility(event, this)" aria-label="Hiện Checksum Key"><span class="material-symbols-outlined" aria-hidden="true">visibility</span></button></div></div>
        </div>
        <p class="notice">Webhook cần trỏ tới: <code>http://<?= ViewHelper::escape((string)($_SERVER['HTTP_HOST'] ?? 'localhost')) ?>/payment/payos/webhook</code></p>
    </section>
    <div class="settings-actions"><button class="button button-primary" type="submit">Lưu cài đặt</button></div>
</form>
