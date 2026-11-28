<nav class="grid gap-1 rounded-2xl border border-slate-200 bg-white p-2.5 shadow-sm shadow-slate-200/60 lg:sticky lg:top-5 lg:gap-1" aria-label="Thông tin và chính sách">
    <strong class="px-2.5 py-2 text-xs font-bold uppercase tracking-wider text-blue-950">Thông tin hỗ trợ</strong>
    <?php
    $primaryLinks = [
        ['/faq', 'faq', 'FAQ'],
        ['/guide', 'guide', 'Hướng dẫn đặt hàng'],
        ['/shipping', 'shipping', 'Vận chuyển'],
        ['/returns', 'returns', 'Đổi trả & hoàn tiền'],
    ];
    foreach ($primaryLinks as $link) {
        $href = $link[0];
        $key = $link[1];
        $label = $link[2];
        $currentAttribute = ($activeInfoPage ?? '') === $key ? 'aria-current="page"' : '';
        ?>
        <a class="rounded-xl px-2.5 py-2.5 text-sm text-slate-600 transition hover:bg-blue-50 hover:text-blue-950 aria-[current=page]:bg-blue-50 aria-[current=page]:font-semibold aria-[current=page]:text-blue-950" href="<?= $href ?>" <?= $currentAttribute ?>><?= $label ?></a>
    <?php } ?>
    <strong class="mt-3 border-t border-slate-100 px-2.5 pt-4 text-xs font-bold uppercase tracking-wider text-blue-950">Thông tin pháp lý</strong>
    <?php
    $secondaryLinks = [
        ['/privacy', 'privacy', 'Chính sách bảo mật'],
        ['/terms', 'terms', 'Điều khoản sử dụng'],
    ];
    foreach ($secondaryLinks as $link) {
        $href = $link[0];
        $key = $link[1];
        $label = $link[2];
        $currentAttribute = ($activeInfoPage ?? '') === $key ? 'aria-current="page"' : '';
        ?>
        <a class="rounded-xl px-2.5 py-2.5 text-sm text-slate-600 transition hover:bg-blue-50 hover:text-blue-950 aria-[current=page]:bg-blue-50 aria-[current=page]:font-semibold aria-[current=page]:text-blue-950" href="<?= $href ?>" <?= $currentAttribute ?>><?= $label ?></a>
    <?php } ?>
</nav>
