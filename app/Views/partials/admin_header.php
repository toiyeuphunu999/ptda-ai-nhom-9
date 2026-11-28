<header class="sticky top-0 z-30 border-b border-[#e7eaf0] bg-white/95 backdrop-blur-md">
    <div class="flex min-h-[82px] items-center gap-4 px-4 sm:px-6 lg:px-10">
        <button class="menu-toggle inline-flex size-10 items-center justify-center rounded-lg border border-[#dfe4ec] bg-white text-[#172033] transition hover:bg-[#f6f7f9] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1e3a5f] lg:hidden" type="button" aria-expanded="false" aria-controls="admin-nav" onclick="return window.toggleNavigationMenu(event, this)">
            <span class="material-symbols-outlined text-xl" aria-hidden="true">menu</span>
            <span class="sr-only">Mở menu</span>
        </button>
        <div class="min-w-0">
            <p class="mb-1 text-[11px] font-semibold uppercase tracking-[.16em] text-[#c2410c]">Khu vực quản trị</p>
            <h1 class="truncate text-2xl font-semibold tracking-[-.03em] text-[#172033] sm:text-3xl"><?= \App\Helpers\ViewHelper::escape($title ?? '') ?></h1>
        </div>
        <span class="ml-auto hidden rounded-full bg-[#f1f5f9] px-3 py-1.5 text-xs font-medium text-[#64748b] sm:inline-flex">Quản lý vận hành</span>
    </div>
</header>