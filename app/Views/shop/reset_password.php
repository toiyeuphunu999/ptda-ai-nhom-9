<?php

use App\Core\Csrf;
use App\Helpers\ViewHelper;
?>
<main class="mx-auto max-w-[1240px] px-4 py-10 sm:px-6 lg:py-16 lg:px-8">
    <div class="mx-auto max-w-xl rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-9">
        <div class="mb-8 flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-100 text-orange-700"><span class="material-symbols-outlined" aria-hidden="true">key</span></div>
        <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-orange-700">Khôi phục tài khoản</p>
        <h1 class="text-3xl font-bold tracking-tight text-slate-950">Đặt lại mật khẩu</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">Tạo mật khẩu mới an toàn để tiếp tục quản lý tài khoản.</p>
        <?php if (!empty($error)): ?><div role="alert" class="mt-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">error</span><span><?= ViewHelper::escape($error) ?></span></div><?php endif; ?>
        <form class="mt-8 space-y-5" method="post" action="/reset-password">
            <?= Csrf::field() ?><input type="hidden" name="token" value="<?= ViewHelper::escape($token ?? '') ?>">
            <div><label class="text-sm font-semibold text-slate-900" for="new-password">Mật khẩu mới</label><input class="mt-2 block min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-100" id="new-password" name="password" type="password" minlength="8" autocomplete="new-password" required></div>
            <div><label class="text-sm font-semibold text-slate-900" for="password-confirmation">Nhập lại mật khẩu mới</label><input class="mt-2 block min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-100" id="password-confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required></div>
            <button class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-orange-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-orange-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-orange-100" type="submit"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">check</span>Lưu mật khẩu mới</button>
        </form>
    </div>
</main>
