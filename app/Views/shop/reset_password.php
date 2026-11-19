<?php use App\Core\Csrf; use App\Helpers\ViewHelper; ?>
<div class="auth-page container">
    <form class="form-card" method="post" action="/reset-password">
        <p class="eyebrow">Khôi phục tài khoản</p>
        <h1>Đặt lại mật khẩu</h1>
        <?php if (!empty($error)): ?><div class="notice"><?= ViewHelper::escape($error) ?></div><?php endif; ?>
        <?= Csrf::field() ?>
        <input type="hidden" name="token" value="<?= ViewHelper::escape($token ?? '') ?>">
        <label for="new-password">Mật khẩu mới</label>
        <input id="new-password" name="password" type="password" minlength="8" autocomplete="new-password" required>
        <label for="password-confirmation">Nhập lại mật khẩu mới</label>
        <input id="password-confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
        <button class="button button-primary" type="submit">Lưu mật khẩu mới</button>
    </form>
</div>
