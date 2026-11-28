<?php

use App\Helpers\ViewHelper;

$pageTitle = $pageTitle ?? $title ?? 'Tài khoản | Mộc Bản';
$pageDescription = $pageDescription ?? 'Đăng nhập hoặc tạo tài khoản Mộc Bản để quản lý đơn hàng và nhận ưu đãi riêng.';
$canonicalPath = $canonicalPath ?? (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$isRegister = ($title ?? '') === 'Đăng ký';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= ViewHelper::escape((string)$pageTitle) ?></title>
    <meta name="description" content="<?= ViewHelper::escape((string)$pageDescription) ?>">
    <link rel="canonical" href="<?= ViewHelper::escape($canonicalPath) ?>">
    <link rel="icon" href="/assets/images/book-placeholder.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/tailwind.generated.css?v=20261128-auth">
</head>
<body class="min-h-screen bg-[#f3f5f7] font-sans text-[#172033] antialiased">
    <main class="grid min-h-screen lg:grid-cols-[minmax(360px,0.82fr)_minmax(0,1.18fr)]">
        <aside class="relative hidden overflow-hidden bg-[#152238] text-white lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16">
            <div class="absolute -right-32 -top-28 size-80 rounded-full bg-[#c2410c]/20 blur-3xl" aria-hidden="true"></div>
            <div class="absolute -bottom-40 -left-24 size-96 rounded-full bg-[#315783]/45 blur-3xl" aria-hidden="true"></div>
            <div class="relative z-10">
                <a class="inline-flex items-center gap-3 no-underline" href="/" aria-label="Mộc Bản - về trang chủ">
                    <span class="grid size-11 place-items-center rounded-2xl bg-[#c2410c] text-xl font-extrabold text-white shadow-[0_12px_30px_-12px_rgba(194,65,12,.8)]">M</span>
                    <span>
                        <span class="block text-2xl font-extrabold tracking-[.16em] text-white">MỘC BẢN</span>
                        <span class="mt-1 block text-[10px] font-semibold uppercase tracking-[.28em] text-[#b8c6d9]">Nhà sách trực tuyến</span>
                    </span>
                </a>
                <div class="mt-24 max-w-[470px] xl:mt-32">
                    <p class="text-xs font-bold uppercase tracking-[.22em] text-[#fb923c]">Một trang riêng cho bạn</p>
                    <h1 class="mt-5 text-4xl font-extrabold leading-[1.12] tracking-[-.04em] text-white xl:text-5xl">Đọc nhiều hơn, sống sâu hơn.</h1>
                    <p class="mt-6 max-w-[410px] text-base leading-8 text-[#c8d4e2]">Lưu lại hành trình đọc sách, theo dõi đơn hàng và nhận những gợi ý vừa vặn với gu của bạn.</p>
                </div>
            </div>
            <div class="relative z-10 grid gap-4 text-sm text-[#c8d4e2]">
                <div class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl bg-white/10 text-[#fb923c]"><span class="material-symbols-outlined text-[20px] leading-none" aria-hidden="true">check</span></span><span>Quản lý đơn hàng gọn gàng</span></div>
                <div class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl bg-white/10 text-[#fb923c]"><span class="material-symbols-outlined text-[20px] leading-none" aria-hidden="true">check</span></span><span>Ưu đãi và điểm thành viên riêng</span></div>
                <p class="pt-5 text-xs text-[#91a3b9]">© <?= date('Y') ?> Mộc Bản. Đọc để lớn lên.</p>
            </div>
        </aside>

        <section class="flex min-h-screen min-w-0 flex-col">
            <header class="flex items-center justify-between px-5 py-5 sm:px-8 sm:py-7 lg:px-12 xl:px-16">
                <a class="inline-flex items-center gap-2.5 no-underline lg:hidden" href="/" aria-label="Mộc Bản - về trang chủ">
                    <span class="grid size-9 place-items-center rounded-xl bg-[#152238] text-base font-extrabold text-white">M</span>
                    <span class="text-base font-extrabold tracking-[.15em] text-[#152238]">MỘC BẢN</span>
                </a>
                <div class="ml-auto flex items-center gap-4 text-sm text-[#64748b]">
                    <span class="hidden sm:inline"><?= $isRegister ? 'Đã có tài khoản?' : 'Chưa có tài khoản?' ?></span>
                    <a class="font-bold text-[#152238] underline decoration-[#c2410c] decoration-2 underline-offset-4 transition hover:text-[#c2410c]" href="<?= $isRegister ? '/login' : '/register' ?>">
                        <?= $isRegister ? 'Đăng nhập' : 'Đăng ký' ?>
                    </a>
                </div>
            </header>

            <div class="flex flex-1 items-start justify-center px-5 pb-10 pt-5 sm:px-8 sm:pb-16 sm:pt-10 lg:items-center lg:px-12 lg:py-10 xl:px-16">
                <div class="w-full min-w-0 max-w-[620px]">
                    <?= $content ?>
                </div>
            </div>

            <footer class="px-5 pb-6 text-center text-xs text-[#94a3b8] sm:px-8 lg:px-12 xl:px-16">
                <a class="transition hover:text-[#152238]" href="/">Về trang chủ</a>
                <span class="mx-2">·</span>
                <span>Hỗ trợ khách hàng: 1900 6868</span>
            </footer>
        </section>
    </main>
    <script src="/assets/js/app.js?v=20261128-auth" defer></script>
</body>
</html>


