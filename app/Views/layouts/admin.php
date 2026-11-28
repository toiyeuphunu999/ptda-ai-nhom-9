<?php use App\Helpers\ViewHelper; ?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= ViewHelper::escape($title ?? 'Quản trị') ?> | Nhà sách Mộc Bản</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jodit@4.6.2/es2021/jodit.min.css">
    <link rel="stylesheet" href="/assets/css/tailwind.generated.css?v=20261128-admin">
</head>
<body class="min-h-screen overflow-x-hidden bg-[#eef1f5] font-sans text-[#172033] antialiased selection:bg-[#ffeddc] selection:text-[#9a3412]">
    <div class="min-h-screen min-w-0 items-start overflow-x-hidden lg:grid lg:grid-cols-[248px_minmax(0,1fr)] lg:transition-[grid-template-columns] lg:duration-500 lg:[transition-timing-function:cubic-bezier(.3,1.3,.45,1)] motion-reduce:transition-none" data-admin-shell>
        <?php require dirname(__DIR__) . '/partials/admin_sidebar.php'; ?>
        <div class="pointer-events-none fixed inset-0 z-40 hidden bg-[#0f2740]/45 opacity-0 transition-opacity data-[open=true]:pointer-events-auto data-[open=true]:block data-[open=true]:opacity-100" data-nav-scrim data-open="false" hidden></div>
        <section class="min-w-0">
            <?php require dirname(__DIR__) . '/partials/admin_header.php'; ?>
            <main class="mx-auto w-full max-w-[1480px] px-4 py-7 sm:px-6 lg:px-10"><?= $content ?></main>
        </section>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jodit@4.6.2/es2021/jodit.min.js" defer></script>
    <script src="/assets/js/jodit-editor.js?v=20261128" defer></script>
    <script src="/assets/js/app.js?v=20261128-admin" defer></script>
</body>
</html>
