<?php
$mobilePath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$mobileTabs = [
    ['label' => 'Trang chủ', 'href' => '/', 'match' => $mobilePath === '/', 'icon' => '<path d="M4 11l8-6.5 8 6.5V19a1 1 0 0 1-1 1h-4.5v-5h-5v5H5a1 1 0 0 1-1-1z"/>'],
    ['label' => 'Khám phá', 'href' => '/books', 'match' => str_starts_with($mobilePath, '/books') || str_starts_with($mobilePath, '/categories') || $mobilePath === '/search', 'icon' => '<circle cx="12" cy="12" r="8.5"/><path d="M15.5 8.5l-2 5-5 2 2-5z"/>'],
    ['label' => 'Yêu thích', 'href' => '/wishlist', 'match' => str_starts_with($mobilePath, '/wishlist'), 'icon' => '<path d="M20.8 8.8c0-2.7-2-4.8-4.6-4.8-1.7 0-3.3.9-4.2 2.3C11.1 4.9 9.5 4 7.8 4 5.2 4 3.2 6.1 3.2 8.8c0 5.1 8.8 10.2 8.8 10.2s8.8-5.1 8.8-10.2z"/>'],
    ['label' => 'Giỏ hàng', 'href' => '/cart', 'match' => str_starts_with($mobilePath, '/cart'), 'icon' => '<path d="M4 5h2l1.5 10.5h9L19 8H7"/><path d="M9 19.5h.01M17 19.5h.01"/>'],
    ['label' => 'Tài khoản', 'href' => '/account', 'match' => str_starts_with($mobilePath, '/account') || $mobilePath === '/login', 'icon' => '<circle cx="12" cy="8.5" r="3.8"/><path d="M4.5 20c.9-3.8 3.8-5.8 7.5-5.8s6.6 2 7.5 5.8"/>'],
];
?>
<nav class="fixed inset-x-0 bottom-0 z-30 flex h-[94px] items-end px-3 pb-[calc(8px+env(safe-area-inset-bottom))] lg:hidden" aria-label="Điều hướng nhanh" data-mobile-tabs>
    <div class="absolute inset-x-3 bottom-2 h-[84px] rounded-[28px] bg-white shadow-[0_24px_40px_-24px_rgba(15,23,42,.7)] ring-1 ring-slate-200"></div>
    <ul class="relative flex h-[84px] w-full items-stretch px-3.5" role="tablist" aria-label="Điều hướng nhanh">
        <?php foreach ($mobileTabs as $index => $tab): ?>
            <li class="flex flex-1" role="none"><a class="group relative flex h-full flex-1 flex-col items-center justify-end pb-3 text-[10px] font-bold text-slate-500 transition focus-visible:rounded-2xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 <?= $tab['match'] ? 'text-slate-900' : '' ?>" href="<?= htmlspecialchars($tab['href'], ENT_QUOTES, 'UTF-8') ?>" role="tab" aria-selected="<?= $tab['match'] ? 'true' : 'false' ?>" aria-current="<?= $tab['match'] ? 'page' : 'false' ?>" tabindex="<?= $tab['match'] ? '0' : '-1' ?>" data-tab-index="<?= $index ?>"><span class="mb-2 grid size-10 place-items-center rounded-full transition duration-300 group-hover:text-blue-700 <?= $tab['match'] ? 'bg-gradient-to-br from-indigo-500 to-blue-500 text-white shadow-lg shadow-blue-500/30 -translate-y-7 ring-8 ring-slate-100' : 'text-slate-500' ?>"><svg class="size-6 fill-none stroke-current stroke-[1.9]" viewBox="0 0 24 24" aria-hidden="true"><?= $tab['icon'] ?></svg></span><span class="<?= $tab['match'] ? '-mt-7' : '' ?>"><?= htmlspecialchars($tab['label'], ENT_QUOTES, 'UTF-8') ?></span><?php if ($tab['href'] === '/cart' && !empty($cartCount)): ?><em class="absolute right-[calc(50%-18px)] top-3 grid min-w-[18px] place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-extrabold leading-[18px] text-white ring-2 ring-white <?= $tab['match'] ? 'scale-0 opacity-0' : '' ?>" aria-label="<?= (int)$cartCount ?> sản phẩm trong giỏ hàng"><?= (int)$cartCount ?></em><?php endif; ?></a></li>
        <?php endforeach; ?>
    </ul>
</nav>


