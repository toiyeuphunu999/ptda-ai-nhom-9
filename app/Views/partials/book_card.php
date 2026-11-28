<?php use App\Core\Csrf; use App\Helpers\ViewHelper; ?>
<article class="book-card group flex min-w-0 h-full flex-col overflow-hidden rounded-2xl border border-[#e7eaf0] bg-white p-3 shadow-[0_10px_28px_-28px_rgba(15,23,42,.75)] transition duration-300 hover:border-[#cbd5e1] hover:shadow-[0_18px_34px_-24px_rgba(30,58,95,.45)] max-[560px]:p-2.5">
    <a class="block overflow-hidden rounded-xl bg-[#eef4ff]" href="/books/<?= ViewHelper::escape($book['slug']) ?>">
        <div class="aspect-[2/3]"><img class="block size-full object-cover" src="/assets/images/book-placeholder.svg" alt="Bìa sách <?= ViewHelper::escape($book['title']) ?>" loading="lazy"></div>
    </a>
    <div class="flex flex-1 flex-col pt-3">
        <h3 class="line-clamp-2 min-h-[2.9em] text-sm font-semibold leading-5 text-[#172033]"><a class="no-underline transition-colors hover:text-[#c2410c]" title="<?= ViewHelper::escape($book['title']) ?>" href="/books/<?= ViewHelper::escape($book['slug']) ?>"><?= ViewHelper::escape($book['title']) ?></a></h3>
        <p class="mt-1 truncate text-xs text-[#64748b]"><?= ViewHelper::escape($book['author']) ?></p>
        <div class="mt-auto pt-3"><p class="text-base font-bold text-[#c2410c]"><?= ViewHelper::money($book['price']) ?></p><?php if ($book['original_price'] > $book['price']): ?><del class="text-xs text-[#64748b]"><?= ViewHelper::money($book['original_price']) ?></del><?php endif; ?></div>
        <?php if (is_array($currentUser ?? null)): ?><form class="mt-3" method="post" action="/wishlist/add/<?= (int)$book['id'] ?>"><?= Csrf::field() ?><button class="inline-flex min-h-9 items-center gap-1.5 rounded-md border border-[#e1e5ea] bg-white px-2.5 text-[11px] font-semibold text-[#1e3a5f] transition hover:border-[#c2410c] hover:text-[#c2410c] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1e3a5f]" type="submit"><span class="material-symbols-outlined text-[17px]" aria-hidden="true">favorite_border</span>Lưu yêu thích</button></form><?php endif; ?>
    </div>
</article>