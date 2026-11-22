<?php use App\Core\Csrf; use App\Helpers\ViewHelper; ?>
<div class="container page-head">
    <?php require dirname(__DIR__) . '/partials/breadcrumbs.php'; ?>
    <p class="eyebrow">Tài khoản</p>
    <h1>Tài khoản cá nhân</h1>
</div>
<section class="container section account-grid">
    <div class="info-card">
        <h2>Thông tin cá nhân</h2>
        <p><strong><?= ViewHelper::escape($user['name'] ?? '') ?></strong></p>
        <p><?= ViewHelper::escape($user['email'] ?? '') ?></p>
        <p>Vai trò: <?= ViewHelper::escape(ViewHelper::userRole((string)($user['role'] ?? 'customer'))) ?></p>
        <p>Mã giới thiệu: <strong><?= ViewHelper::escape($user['referral_code'] ?? 'Chưa có') ?></strong></p>
        <?php if (!empty($profileError)): ?><div class="notice"><?= ViewHelper::escape($profileError) ?></div><?php endif; ?>
        <form id="account-profile-form" class="account-profile-form" method="post" action="/account">
            <?= Csrf::field() ?>
            <label for="account-name">Họ và tên</label>
            <input id="account-name" name="name" type="text" maxlength="120" value="<?= ViewHelper::escape((string)($formName ?? $user['name'] ?? '')) ?>" required>
            <label for="account-phone">Số điện thoại nhận hàng</label>
            <input id="account-phone" name="phone" type="tel" maxlength="30" autocomplete="tel" value="<?= ViewHelper::escape((string)($formPhone ?? $user['phone'] ?? '')) ?>" placeholder="0901 234 567">
            <label for="account-address">Địa chỉ nhận hàng mặc định</label>
            <textarea id="account-address" name="address" rows="3" maxlength="255" autocomplete="street-address" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"><?= ViewHelper::escape((string)($formAddress ?? $user['address'] ?? '')) ?></textarea>
            <p class="form-hint">Thông tin này sẽ được tự điền ở bước thanh toán. Bạn vẫn có thể sửa lại cho từng đơn hàng.</p>
        </form>
        <form id="account-logout-form" class="account-logout-form" method="post" action="/logout">
            <?= Csrf::field() ?>
        </form>
        <div class="account-profile-actions"><button class="button" type="submit" form="account-profile-form">Lưu họ tên</button><button class="button" type="submit" form="account-logout-form">Đăng xuất</button></div>
    </div>
    <div class="info-card">
        <h2>Điểm thưởng</h2>
        <p class="book-price"><?= (int)($pointsBalance ?? 0) ?> điểm</p>
        <p>Điểm được cộng khi hoàn tất đơn hàng, đánh giá sách và giới thiệu thành viên mới.</p>
        <a href="/orders">Xem lịch sử đơn hàng</a>
    </div>
</section>
<section class="container section address-section">
    <div class="section-heading"><div><p class="eyebrow">Giao hàng</p><h2>Địa chỉ nhận hàng</h2></div></div>
    <div class="address-list">
        <?php foreach (($addresses ?? []) as $savedAddress): ?>
            <article class="info-card saved-address-card">
                <div class="address-card-heading"><div><h3><?= ViewHelper::escape($savedAddress['label']) ?></h3><?php if ((int)$savedAddress['is_default'] === 1): ?><span class="report-badge report-badge-regular">Mặc định</span><?php endif; ?></div><span class="material-symbols-outlined" aria-hidden="true">location_on</span></div>
                <p><strong><?= ViewHelper::escape($savedAddress['recipient_name']) ?></strong> · <?= ViewHelper::escape($savedAddress['phone']) ?></p><p><?= ViewHelper::escape($savedAddress['address']) ?></p>
                <details><summary>Chỉnh sửa</summary><form class="address-form" method="post" action="/account/addresses/<?= (int)$savedAddress['id'] ?>/update"><?= Csrf::field() ?><label>Tên địa chỉ<input name="label" maxlength="50" value="<?= ViewHelper::escape($savedAddress['label']) ?>" required></label><label>Người nhận<input name="recipient_name" maxlength="120" value="<?= ViewHelper::escape($savedAddress['recipient_name']) ?>" required></label><label>Số điện thoại<input name="phone" maxlength="30" value="<?= ViewHelper::escape($savedAddress['phone']) ?>" required></label><label>Địa chỉ<textarea name="address" rows="3" maxlength="255" required><?= ViewHelper::escape($savedAddress['address']) ?></textarea></label><button class="button button-primary" type="submit">Lưu thay đổi</button></form></details>
                <div class="address-actions"><?php if ((int)$savedAddress['is_default'] !== 1): ?><form method="post" action="/account/addresses/<?= (int)$savedAddress['id'] ?>/default"><?= Csrf::field() ?><button class="button" type="submit">Đặt mặc định</button></form><?php endif; ?><form method="post" action="/account/addresses/<?= (int)$savedAddress['id'] ?>/delete" onsubmit="return confirm('Xóa địa chỉ này?');"><?= Csrf::field() ?><button class="button button-danger" type="submit">Xóa</button></form></div>
            </article>
        <?php endforeach; ?>
    </div>
    <form class="form-card address-form address-create-form" method="post" action="/account/addresses"><h3>Thêm địa chỉ mới</h3><?= Csrf::field() ?><label for="address-label">Tên địa chỉ</label><input id="address-label" name="label" maxlength="50" placeholder="Nhà riêng, Công ty..." required><label for="address-recipient">Người nhận</label><input id="address-recipient" name="recipient_name" maxlength="120" value="<?= ViewHelper::escape($user['name'] ?? '') ?>" required><label for="address-phone">Số điện thoại</label><input id="address-phone" name="phone" maxlength="30" value="<?= ViewHelper::escape($user['phone'] ?? '') ?>" required><label for="address-value">Địa chỉ</label><textarea id="address-value" name="address" rows="3" maxlength="255" required></textarea><label class="checkbox-label"><input type="checkbox" name="is_default" value="1"> Đặt làm địa chỉ mặc định</label><button class="button button-primary" type="submit">Thêm địa chỉ</button></form>
</section>
<section class="container section">
    <div class="section-heading"><div><p class="eyebrow">Lịch sử</p><h2>Biến động điểm thưởng</h2></div></div>
    <?php if (empty($pointsHistory)): ?>
        <div class="empty-state"><p>Bạn chưa có giao dịch điểm thưởng.</p></div>
    <?php else: ?>
        <div class="table-wrap"><table class="admin-table"><thead><tr><th>Nội dung</th><th>Điểm</th><th>Thời gian</th></tr></thead><tbody>
        <?php foreach ($pointsHistory as $transaction): ?>
            <tr><td><?= ViewHelper::escape($transaction['description']) ?></td><td><?= (int)$transaction['points'] > 0 ? '+' : '' ?><?= (int)$transaction['points'] ?></td><td><?= ViewHelper::escape($transaction['created_at']) ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
