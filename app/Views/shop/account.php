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
