<?php
use App\Helpers\ViewHelper;

$pageTitle = $pageTitle ?? $title ?? 'Nhà sách Mộc Bản';
$pageDescription = $pageDescription ?? 'Nhà sách Mộc Bản - tìm và đặt sách trực tuyến.';
$canonicalPath = $canonicalPath ?? (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= ViewHelper::escape($pageTitle) ?></title>
    <meta name="description" content="<?= ViewHelper::escape($pageDescription) ?>">
    <link rel="canonical" href="<?= ViewHelper::escape($canonicalPath) ?>">
    <link rel="icon" href="/assets/images/book-placeholder.svg" type="image/svg+xml">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= ViewHelper::escape($pageTitle) ?>">
    <meta property="og:description" content="<?= ViewHelper::escape($pageDescription) ?>">
    <meta property="og:url" content="<?= ViewHelper::escape($canonicalPath) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/tailwind.generated.css?v=20261128-home">
</head>
<body class="min-h-screen overflow-x-hidden bg-[#f3f5f7] font-sans text-[#172033] antialiased selection:bg-[#ffeddc] selection:text-[#9a3412] max-[800px]:pb-24">
    <?php require dirname(__DIR__) . '/partials/shop_header.php'; ?>
    <div class="nav-scrim pointer-events-none fixed inset-0 z-50 hidden bg-[#022448]/45 opacity-0 transition-opacity data-[open=true]:pointer-events-auto data-[open=true]:block data-[open=true]:opacity-100" data-nav-scrim data-open="false" hidden></div>
    <main><?= $content ?></main>
    <?php require dirname(__DIR__) . '/partials/shop_footer.php'; ?>
    <?php require dirname(__DIR__) . '/partials/mobile_bottom_tabs.php'; ?>
    <script src="/assets/js/app.js?v=20261128-home" defer></script>
    <script src="/assets/js/star-rating.js?v=20261128-rating" defer></script>
    <script src="/assets/js/mobile-bottom-tabs.js?v=20261128-tabs" defer></script>
</body>
</html>
