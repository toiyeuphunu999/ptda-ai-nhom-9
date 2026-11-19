<?php use App\Core\Csrf; use App\Helpers\ViewHelper; ?>
<div class="auth-page container">
    <form class="form-card" method="post" action="/forgot-password">
        <p class="eyebrow">Khôi phục tài khoản</p>
        <h1>Quên mật khẩu</h1>
        <p class="muted">Nhập email đã đăng ký. Nếu tài khoản tồn tại, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu.</p>
        <?php if (!empty($error)): ?><div class="notice"><?= ViewHelper::escape($error) ?></div><?php endif; ?>
        <?= Csrf::field() ?>
        <label for="forgot-email">Email đăng ký</label>
        <input id="forgot-email" name="email" type="email" value="<?= ViewHelper::escape($email ?? '') ?>" autocomplete="email" required>
        <button class="button button-primary" type="submit">Gửi link đặt lại mật khẩu</button>
        <p><a href="/login">Quay lại đăng nhập</a></p>
    </form>
</div>
