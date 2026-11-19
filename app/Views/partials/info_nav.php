<nav class="info-nav" aria-label="Thông tin và chính sách">
    <strong>Thông tin hỗ trợ</strong>
    <a href="/faq" <?= ($activeInfoPage ?? '') === 'faq' ? 'aria-current="page"' : '' ?>>FAQ</a>
    <a href="/guide" <?= ($activeInfoPage ?? '') === 'guide' ? 'aria-current="page"' : '' ?>>Hướng dẫn đặt hàng</a>
    <a href="/shipping" <?= ($activeInfoPage ?? '') === 'shipping' ? 'aria-current="page"' : '' ?>>Vận chuyển</a>
    <a href="/returns" <?= ($activeInfoPage ?? '') === 'returns' ? 'aria-current="page"' : '' ?>>Đổi trả & hoàn tiền</a>
    <strong>Thông tin pháp lý</strong>
    <a href="/privacy" <?= ($activeInfoPage ?? '') === 'privacy' ? 'aria-current="page"' : '' ?>>Chính sách bảo mật</a>
    <a href="/terms" <?= ($activeInfoPage ?? '') === 'terms' ? 'aria-current="page"' : '' ?>>Điều khoản sử dụng</a>
</nav>
