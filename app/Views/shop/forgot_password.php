<?php

use App\Core\Csrf;
use App\Helpers\ViewHelper;
?>
<main class="mx-auto max-w-[1240px] px-4 py-10 sm:px-6 lg:py-16 lg:px-8">
    <div class="mx-auto max-w-xl rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-9">
        <div class="mb-8 flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-100 text-orange-700"><span class="material-symbols-outlined" aria-hidden="true">lock_reset</span></div>
        <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-orange-700">Khôi phục tài khoản</p>
        <h1 class="text-3xl font-bold tracking-tight text-slate-950">Quên mật khẩu?</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">Nhập email đã đăng ký, chúng tôi sẽ gửi cho bạn liên kết đặt lại mật khẩu.</p>
        <?php if (!empty($error)): ?><div role="alert" class="mt-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">error</span><span><?= ViewHelper::escape($error) ?></span></div><?php endif; ?>
        <form class="mt-8 space-y-5" method="post" action="/forgot-password">
            <?= Csrf::field() ?>
            <div><label class="text-sm font-semibold text-slate-900" for="forgot-email">Email</label><input class="mt-2 block min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-100" id="forgot-email" name="email" type="email" value="<?= ViewHelper::escape($email ?? '') ?>" autocomplete="email" placeholder="you@example.com" required></div>
            <button class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-orange-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-orange-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-orange-100" type="submit"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">send</span>Gửi link đặt lại mật khẩu</button>
        </form>
        <a class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-blue-800 hover:text-blue-950" href="/login"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>Quay lại đăng nhập</a>
    </div>
</main>
