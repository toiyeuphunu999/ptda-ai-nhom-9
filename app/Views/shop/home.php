<?php use App\Helpers\ViewHelper; ?>
<section class="mx-auto w-[min(1280px,calc(100%-40px))] py-12 max-[800px]:w-[calc(100%-28px)] max-[800px]:py-8">
    <div class="grid grid-cols-[minmax(0,1.55fr)_minmax(280px,.9fr)] gap-6 max-[800px]:grid-cols-1">
        <div class="relative flex min-h-[390px] flex-col justify-between overflow-hidden rounded-[32px] bg-[#152238] p-10 text-white shadow-[0_26px_55px_-34px_rgba(21,34,56,.8)] max-[560px]:min-h-[350px] max-[560px]:p-6">
            <div class="pointer-events-none absolute -right-24 -top-24 size-72 rounded-full bg-[#c2410c]/20 blur-3xl"></div>
            <div>
                <p class="mb-4 text-[11px] font-bold uppercase tracking-[.16em] text-[#fdba74]">Lễ hội sách Mộc Bản</p>
                <h1 class="relative max-w-2xl text-[clamp(2.25rem,4.5vw,4.4rem)] font-semibold leading-[1.04] tracking-[-.055em]">Ưu đãi tới 50% cho những đầu sách tinh hoa</h1>
                <p class="mt-5 max-w-xl text-[15px] leading-7 text-[#dce9ff]">Khám phá những cuốn sách phù hợp với nhịp đọc của bạn, từ văn học, kỹ năng đến công nghệ.</p>
            </div>
            <a class="relative mt-8 inline-flex w-fit items-center gap-2 rounded-xl bg-[#c2410c] px-5 py-3 text-sm font-bold text-white no-underline shadow-[0_12px_26px_-14px_rgba(194,65,12,.9)] transition hover:bg-[#9a3412] hover:shadow-[0_16px_30px_-14px_rgba(154,52,18,.9)] focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-[#fdba74]" href="/books">Khám phá ngay <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span></a>
        </div>
        <div class="grid gap-5 max-[800px]:grid-cols-2 max-[560px]:grid-cols-1">
            <a class="flex min-h-[182px] flex-col justify-between rounded-[28px] bg-[#e8f0ff] p-7 text-[#1e3a5f] no-underline transition hover:bg-[#dbe7ff] hover:shadow-[0_18px_30px_-24px_rgba(30,58,95,.7)]" href="/categories/kinh-te">
                <span class="text-[11px] font-bold uppercase tracking-[.14em] text-[#1e3a5f]">Kinh tế &amp; tài chính</span>
                <span><strong class="block text-xl font-semibold tracking-[-.02em]">Đọc để hiểu, chọn để tiến xa</strong><small class="mt-2 block text-sm leading-6 text-[#475569]">Tuyển chọn sách kinh doanh và phát triển bản thân.</small></span>
            </a>
            <a class="flex min-h-[182px] flex-col justify-between rounded-[28px] bg-[#fff0e5] p-7 text-[#1e3a5f] no-underline transition hover:bg-[#f6d3b5] hover:shadow-[0_18px_30px_-24px_rgba(194,65,12,.45)]" href="/categories/thieu-nhi">
                <span class="text-[11px] font-bold uppercase tracking-[.14em] text-[#9a3412]">Tuổi thơ &amp; khám phá</span>
                <span><strong class="block text-xl font-semibold tracking-[-.02em]">Nuôi dưỡng trí tưởng tượng</strong><small class="mt-2 block text-sm leading-6 text-[#475569]">Những cuốn sách dành cho độc giả nhỏ tuổi.</small></span>
            </a>
        </div>
    </div>
</section>

<section class="mx-auto w-[min(1280px,calc(100%-40px))] py-10 max-[800px]:w-[calc(100%-28px)] max-[800px]:py-7">
    <div class="mb-6 flex items-end justify-between gap-4 border-b border-[#dfe3e8] pb-4">
        <div><p class="mb-2 text-[11px] font-bold uppercase tracking-[.16em] text-[#c2410c]">Khám phá</p><h2 class="text-2xl font-semibold tracking-[-.03em] text-[#1e3a5f]">Danh mục sách</h2></div>
        <a class="inline-flex min-h-11 items-center gap-1.5 text-sm font-bold text-[#c2410c] no-underline hover:text-[#9a3412]" href="/books">Xem tất cả <span class="material-symbols-outlined text-[18px] leading-none" aria-hidden="true">arrow_forward</span></a>
    </div>
    <div class="grid grid-cols-4 gap-4 max-[800px]:grid-cols-2 max-[560px]:grid-cols-1">
        <?php foreach ($categories as $category): ?>
            <a class="flex min-h-28 flex-col justify-between rounded-xl border border-[#eef0f2] bg-white p-5 text-[#1e3a5f] no-underline transition hover:border-[#c2410c] hover:shadow-[0_14px_28px_-24px_rgba(30,58,95,.7)]" href="/categories/<?= ViewHelper::escape($category['slug']) ?>"><span class="font-semibold"><?= ViewHelper::escape($category['name']) ?></span><span class="material-symbols-outlined text-2xl text-[#c2410c] leading-none" aria-hidden="true">arrow_forward</span></a>
        <?php endforeach; ?>
    </div>
</section>

<section class="mx-auto w-[min(1280px,calc(100%-40px))] py-10 max-[800px]:w-[calc(100%-28px)] max-[800px]:py-7">
    <div class="mb-6 flex items-end justify-between gap-4 border-b border-[#dfe3e8] pb-4">
        <div><p class="mb-2 text-[11px] font-bold uppercase tracking-[.16em] text-[#c2410c]">Gợi ý đọc</p><h2 class="text-2xl font-semibold tracking-[-.03em] text-[#1e3a5f]">Sách được quan tâm</h2></div>
        <a class="inline-flex min-h-11 items-center gap-1.5 text-sm font-bold text-[#c2410c] no-underline hover:text-[#9a3412]" href="/books">Xem toàn bộ <span class="material-symbols-outlined text-[18px] leading-none" aria-hidden="true">arrow_forward</span></a>
    </div>
    <div class="book-grid grid grid-cols-5 items-stretch gap-5 max-[1050px]:grid-cols-4 max-[800px]:grid-cols-3 max-[560px]:grid-cols-2 max-[560px]:gap-3" aria-busy="false">
        <?php foreach (array_slice($books, 0, 10) as $book): ?><?php require dirname(__DIR__) . '/partials/book_card.php'; ?><?php endforeach; ?>
    </div>
</section>