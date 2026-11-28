<?php
$headerUser = $currentUser ?? null;
$isAdmin = is_array($headerUser) && ($headerUser['role'] ?? '') === 'admin';
$accountUrl = is_array($headerUser) ? '/account' : '/login';
$accountLabel = is_array($headerUser) ? 'Tài khoản' : 'Đăng nhập';
?>
<header class="sticky top-0 z-30 border-b border-[#e7eaf0] bg-white/95 shadow-[0_8px_30px_-28px_rgba(15,23,42,.7)] backdrop-blur-md">
    <div class="bg-[#1e3a5f] text-[11px] text-[#e5eeff]">
        <div class="mx-auto flex min-h-8 w-[min(1280px,calc(100%-40px))] items-center justify-between gap-4 max-[800px]:w-[calc(100%-28px)]">
            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px]" aria-hidden="true">local_shipping</span>Vận chuyển miễn phí từ 250.000₫</span>
            <div class="flex items-center gap-5 max-[800px]:hidden">
                <a class="inline-flex items-center gap-1.5 hover:text-white" href="/contact"><span class="material-symbols-outlined text-[16px]" aria-hidden="true">help</span>Trợ giúp</a>
                <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px]" aria-hidden="true">support_agent</span>Hotline: 1900 6868</span>
            </div>
        </div>
    </div>

    <div class="border-b border-[#eef0f2] bg-white">
        <div class="mx-auto flex min-h-[82px] w-[min(1280px,calc(100%-40px))] items-center gap-8 max-[800px]:grid max-[800px]:grid-cols-[1fr_auto] max-[800px]:w-[calc(100%-28px)] max-[800px]:gap-3 max-[800px]:py-3">
            <a class="shrink-0 text-[#1e3a5f] no-underline" href="/">
                <span class="block text-[1.35rem] font-bold tracking-[.11em]">MỘC BẢN</span>
                <small class="mt-0.5 block text-[10px] font-medium tracking-[.18em] text-[#64748b]">NHÀ SÁCH TRỰC TUYẾN</small>
            </a>

            <form class="u429 order-2 relative flex h-14 min-w-0 flex-1 items-center overflow-visible rounded-2xl border border-[#dfe4ec] bg-[#f8fafc] transition duration-200 focus-within:border-[#1e3a5f] focus-within:bg-white focus-within:ring-4 focus-within:ring-[#e5eeff] max-[800px]:order-3 max-[800px]:col-span-2" action="/search" method="get" data-search-autocomplete>
                <label class="sr-only" for="site-search">Tìm sách</label>
                <span class="pointer-events-none ml-3 grid size-5 shrink-0 place-items-center self-center text-[#64748b]" aria-hidden="true"><span class="material-symbols-outlined text-[20px] leading-none">search</span></span>
                <input class="u429-in h-full min-w-0 flex-1 border-0 bg-transparent px-2 py-0 text-base leading-6 outline-none placeholder:text-[#64748b] md:text-sm" id="site-search" type="search" name="q" role="combobox" aria-expanded="false" aria-autocomplete="list" aria-controls="site-search-results" placeholder="Tìm tên sách hoặc tác giả..." autocomplete="off" spellcheck="false">
                <button class="u471-b u429-kb mr-2 hidden h-9 shrink-0 items-center gap-1 rounded-lg border-0 bg-[#f1f5f9] px-2 text-[11px] font-semibold leading-none text-[#64748b] shadow-[inset_0_0_0_1px_#e2e8f0] transition duration-500 [transition-timing-function:cubic-bezier(.34,1.7,.5,1)] hover:bg-[#e5eeff] hover:text-[#1e3a5f] active:scale-[.975] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1e3a5f] sm:inline-flex motion-reduce:transition-none" type="button" aria-label="Mở tìm kiếm" aria-keyshortcuts="Control+K" data-command-k><span class="u471-key rounded-md bg-white px-1.5 py-1 leading-none shadow-[inset_0_0_0_1px_#dfe4ec] transition-transform duration-150 [transition-timing-function:cubic-bezier(.3,1.9,.5,1)] motion-reduce:transition-none" data-command-mod>Ctrl</span><span class="u471-key rounded-md bg-white px-1.5 py-1 leading-none shadow-[inset_0_0_0_1px_#dfe4ec] transition-transform duration-150 [transition-timing-function:cubic-bezier(.3,1.9,.5,1)] motion-reduce:transition-none">K</span></button>
                <button class="my-1 mr-1 inline-flex h-12 min-h-0 shrink-0 items-center gap-1.5 rounded-xl bg-[#c2410c] px-5 font-semibold leading-none text-white transition hover:bg-[#9a3412] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1e3a5f]" type="submit"><span class="material-symbols-outlined text-[19px] leading-none" aria-hidden="true">search</span>Tìm</button>
                <div class="u429-dd invisible absolute left-0 right-0 top-full z-50 mt-2 origin-top scale-[.98] rounded-xl border border-[#e2e8f0] bg-white p-1.5 opacity-0 shadow-[0_22px_44px_-18px_rgba(15,23,42,.35)] transition duration-200 [transition-timing-function:cubic-bezier(.3,1.4,.5,1)] data-[open=true]:visible data-[open=true]:scale-100 data-[open=true]:opacity-100" data-search-results data-open="false" role="listbox"></div>
            </form>

            <div class="order-3 ml-auto flex items-center gap-5 max-[1050px]:gap-3 max-[800px]:order-2 max-[800px]:ml-auto max-[560px]:gap-2">
                <a class="group flex items-center gap-2 text-[#64748b] no-underline max-[560px]:size-10 max-[560px]:justify-center" href="<?= \App\Helpers\ViewHelper::escape($accountUrl) ?>">
                    <span class="material-symbols-outlined text-[24px] text-[#1e3a5f]" aria-hidden="true">account_circle</span><span class="max-[1050px]:hidden"><small class="block text-[11px]">Tài khoản</small><strong class="block text-sm text-[#1e3a5f]"><?= \App\Helpers\ViewHelper::escape($accountLabel) ?></strong></span>
                </a>
                <?php if (is_array($headerUser)): ?><a class="flex items-center gap-2 text-[#64748b] no-underline max-[560px]:size-10 max-[560px]:justify-center" href="/wishlist"><span class="material-symbols-outlined text-[24px] text-[#1e3a5f]" aria-hidden="true">favorite</span><span class="max-[1050px]:hidden"><small class="block text-[11px]">Danh sách</small><strong class="block text-sm text-[#1e3a5f]">Yêu thích</strong></span></a><?php endif; ?>
                <a class="flex items-center gap-2 text-[#64748b] no-underline max-[560px]:size-10 max-[560px]:justify-center" href="/cart"><span class="material-symbols-outlined text-[24px] text-[#1e3a5f]" aria-hidden="true">shopping_bag</span><span class="max-[1050px]:hidden"><small class="block text-[11px]">Giỏ hàng</small><strong class="block text-sm text-[#1e3a5f]"><?= $cartCount ?> sản phẩm</strong></span></a>
                <?php if ($isAdmin): ?><a class="hidden items-center gap-2 text-[#64748b] no-underline min-[1051px]:flex" href="/admin"><span class="material-symbols-outlined text-[24px] text-[#1e3a5f]" aria-hidden="true">admin_panel_settings</span><strong class="text-sm text-[#1e3a5f]">Quản trị</strong></a><?php endif; ?>
                <button class="hidden size-10 items-center justify-center rounded-md border border-[#e5e7eb] bg-white text-[#1e3a5f] max-[800px]:inline-flex" type="button" aria-expanded="false" aria-controls="main-nav" aria-label="Mở menu" onclick="return window.toggleNavigationMenu(event, this)"><span class="grid w-5 gap-1" aria-hidden="true"><i class="block h-0.5 w-full bg-current"></i><i class="block h-0.5 w-full bg-current"></i><i class="block h-0.5 w-full bg-current"></i></span></button>
            </div>
        </div>
    </div>

</header>

    <div class="border-b border-[#e7eaf0] bg-[#f8fafc]">
        <div class="relative mx-auto flex min-h-12 w-[min(1280px,calc(100%-40px))] items-center gap-5 max-[800px]:w-[calc(100%-28px)]">
            <button class="inline-flex min-h-10 items-center gap-2 rounded-md bg-[#1e3a5f] px-4 text-xs font-bold tracking-wide text-white transition hover:bg-[#022448] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1e3a5f] max-[800px]:hidden" type="button"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">menu_book</span>Danh mục sách</button>
            <nav id="main-nav" class="main-nav relative z-[60] flex min-w-0 items-center gap-1 overflow-x-auto whitespace-nowrap max-[800px]:fixed max-[800px]:top-0 max-[800px]:bottom-0 max-[800px]:left-0 max-[800px]:hidden max-[800px]:h-[100dvh] max-[800px]:max-h-[100dvh] max-[800px]:w-[min(320px,86vw)] max-[800px]:flex-col max-[800px]:items-stretch max-[800px]:gap-1 max-[800px]:overflow-y-auto max-[800px]:overscroll-contain max-[800px]:bg-[#152238] max-[800px]:p-5 max-[800px]:shadow-2xl max-[800px]:[&.is-open]:flex" data-drawer aria-label="Điều hướng chính">
                <div class="drawer-header mb-3 flex items-center justify-between border-b border-[#38536d] pb-3 text-white min-[801px]:hidden"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#fb923c]" aria-hidden="true">menu_book</span><strong>Danh mục sách</strong></div><button class="drawer-close grid size-9 place-items-center rounded-md border border-[#38536d] bg-[#20314b] text-[#dbe7f3] transition hover:bg-[#2b4160]" type="button" aria-label="Đóng menu"><span class="material-symbols-outlined" aria-hidden="true">close</span></button></div>
                <a class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-[#aebed0] transition hover:bg-[#20314b] hover:text-white max-[800px]:min-h-11 max-[800px]:border-0 max-[800px]:font-semibold" href="/books"><span class="material-symbols-outlined text-[20px] max-[800px]:text-[#fb923c]" aria-hidden="true">local_fire_department</span><span>Sách bán chạy</span></a>
                <a class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-[#aebed0] transition hover:bg-[#20314b] hover:text-white max-[800px]:min-h-11 max-[800px]:border-0 max-[800px]:font-semibold" href="/books?sort=newest"><span class="material-symbols-outlined text-[20px] max-[800px]:text-[#fb923c]" aria-hidden="true">auto_awesome</span><span>Sách mới</span></a>
                <a class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-[#aebed0] transition hover:bg-[#20314b] hover:text-white max-[800px]:min-h-11 max-[800px]:border-0 max-[800px]:font-semibold" href="/categories/van-hoc"><span class="material-symbols-outlined text-[20px] max-[800px]:text-[#fb923c]" aria-hidden="true">menu_book</span><span>Văn học</span></a>
                <a class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-[#aebed0] transition hover:bg-[#20314b] hover:text-white max-[800px]:min-h-11 max-[800px]:border-0 max-[800px]:font-semibold" href="/categories/kinh-te"><span class="material-symbols-outlined text-[20px] max-[800px]:text-[#fb923c]" aria-hidden="true">trending_up</span><span>Kinh tế</span></a>
                <a class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-[#aebed0] transition hover:bg-[#20314b] hover:text-white max-[800px]:min-h-11 max-[800px]:border-0 max-[800px]:font-semibold" href="/categories/ky-nang-song"><span class="material-symbols-outlined text-[20px] max-[800px]:text-[#fb923c]" aria-hidden="true">psychology</span><span>Kỹ năng sống</span></a>
                <a class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-[#aebed0] transition hover:bg-[#20314b] hover:text-white max-[800px]:min-h-11 max-[800px]:border-0 max-[800px]:font-semibold" href="/categories/thieu-nhi"><span class="material-symbols-outlined text-[20px] max-[800px]:text-[#fb923c]" aria-hidden="true">child_care</span><span>Sách thiếu nhi</span></a>
                <a class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-[#aebed0] transition hover:bg-[#20314b] hover:text-white max-[800px]:min-h-11 max-[800px]:border-0 max-[800px]:font-semibold" href="/search"><span class="material-symbols-outlined text-[20px] max-[800px]:text-[#fb923c]" aria-hidden="true">search</span><span>Tìm kiếm</span></a>
            </nav>
        </div>
    </div>
<script src="/assets/js/search-autocomplete.js?v=20261128-search" defer></script>
