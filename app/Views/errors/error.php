<?php
use App\Helpers\ViewHelper;

$errorCode = (string)($errorCode ?? '404');
$errorTitle = (string)($errorTitle ?? 'Không tìm thấy trang');
$errorMessage = (string)($errorMessage ?? 'Đường dẫn bạn truy cập không tồn tại trên website.');
$errorLabel = (string)($errorLabel ?? 'Có thể đường dẫn đã thay đổi hoặc không còn tồn tại.');
$primaryUrl = (string)($primaryUrl ?? '/');
$primaryLabel = (string)($primaryLabel ?? 'Về trang chủ');
?>
<main class="error-page-wrap">
    <section class="error-page container" aria-labelledby="error-title">
        <div class="error-mark"><span class="material-symbols-outlined" aria-hidden="true">menu_book</span></div>
        <p class="error-code"><?= ViewHelper::escape($errorCode) ?></p>
        <p class="eyebrow">Nhà sách Mộc Bản</p>
        <h1 id="error-title"><?= ViewHelper::escape($errorTitle) ?></h1>
        <p class="error-message"><?= ViewHelper::escape($errorMessage) ?></p>
        <p class="error-label"><?= ViewHelper::escape($errorLabel) ?></p>
        <div class="error-actions">
            <a class="button button-primary" href="<?= ViewHelper::escape($primaryUrl) ?>"><span class="material-symbols-outlined" aria-hidden="true">home</span><?= ViewHelper::escape($primaryLabel) ?></a>
            <a class="button" href="/books"><span class="material-symbols-outlined" aria-hidden="true">menu_book</span>Khám phá sách</a>
        </div>
    </section>
</main>
