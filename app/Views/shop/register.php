<?php

use App\Core\Csrf;
use App\Helpers\ViewHelper;
?>
<form class="grid gap-5 rounded-3xl border border-[#e2e8f0] bg-white p-6 shadow-[0_24px_70px_-38px_rgba(21,34,56,.45)] sm:p-9" method="post" action="/register">
    <div>
        <p class="text-xs font-bold uppercase tracking-[.2em] text-[#c2410c]">Tài khoản</p>
        <h1 class="mt-3 text-3xl font-extrabold tracking-[-.04em] text-[#152238] sm:text-4xl">Tạo tài khoản mới</h1>
        <p class="mt-3 text-sm leading-6 text-[#64748b]">Mở khóa trải nghiệm mua sắm tiện lợi và những gợi ý sách dành riêng cho bạn.</p>
    </div>
    <?php if (!empty($error)): ?>
        <div class="flex items-start gap-3 rounded-2xl border border-[#fed7aa] bg-[#fff7ed] px-4 py-3 text-sm leading-6 text-[#9a3412]" role="alert">
            <span class="mt-0.5 font-bold">!</span>
            <span><?= ViewHelper::escape($error) ?></span>
        </div>
    <?php endif; ?>
    <?= Csrf::field() ?>
    <div class="grid gap-5 sm:grid-cols-2">
        <div class="grid gap-2 sm:col-span-2">
            <label class="text-sm font-semibold text-[#172033]" for="fullname">Họ và tên</label>
            <input class="min-h-12 rounded-xl border border-[#cbd5e1] bg-white px-4 text-base text-[#172033] outline-none transition placeholder:text-[#94a3b8] hover:border-[#94a3b8] focus:border-[#152238] focus:ring-4 focus:ring-[#dbeafe]" id="fullname" name="name" type="text" value="<?= ViewHelper::escape((string)($name ?? '')) ?>" autocomplete="name" placeholder="Nguyễn Văn A" required>
        </div>
        <div class="grid gap-2 sm:col-span-2">
            <label class="text-sm font-semibold text-[#172033]" for="email">Email</label>
            <input class="min-h-12 rounded-xl border border-[#cbd5e1] bg-white px-4 text-base text-[#172033] outline-none transition placeholder:text-[#94a3b8] hover:border-[#94a3b8] focus:border-[#152238] focus:ring-4 focus:ring-[#dbeafe]" id="email" name="email" type="email" value="<?= ViewHelper::escape((string)($email ?? '')) ?>" autocomplete="email" placeholder="you@example.com" required>
        </div>
        <div class="grid gap-2 sm:col-span-2">
            <label class="text-sm font-semibold text-[#172033]" for="password">Mật khẩu</label>
            <input class="min-h-12 rounded-xl border border-[#cbd5e1] bg-white px-4 text-base text-[#172033] outline-none transition placeholder:text-[#94a3b8] hover:border-[#94a3b8] focus:border-[#152238] focus:ring-4 focus:ring-[#dbeafe]" id="password" name="password" type="password" minlength="8" autocomplete="new-password" placeholder="Tối thiểu 8 ký tự" required>
        </div>
        <div class="grid gap-2 sm:col-span-2">
            <label class="text-sm font-semibold text-[#172033]" for="referral-code">Mã giới thiệu <span class="font-normal text-[#94a3b8]">(nếu có)</span></label>
            <input class="min-h-12 rounded-xl border border-[#cbd5e1] bg-white px-4 text-base text-[#172033] outline-none transition placeholder:text-[#94a3b8] hover:border-[#94a3b8] focus:border-[#152238] focus:ring-4 focus:ring-[#dbeafe]" id="referral-code" name="referral_code" type="text" maxlength="20" value="<?= ViewHelper::escape((string)($_POST['referral_code'] ?? '')) ?>" placeholder="Ví dụ: MB000001">
        </div>
    </div>
    <button class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#c2410c] px-5 text-sm font-bold text-white shadow-[0_12px_24px_-14px_rgba(194,65,12,.8)] transition hover:-translate-y-0.5 hover:bg-[#9a3412] focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-[#152238] active:translate-y-0" type="submit">Tạo tài khoản</button>
    <div class="flex items-center gap-3 text-xs font-semibold uppercase tracking-[.14em] text-[#94a3b8]" aria-hidden="true">
        <span class="h-px flex-1 bg-[#e2e8f0]"></span>
        <span>Hoặc đăng ký với</span>
        <span class="h-px flex-1 bg-[#e2e8f0]"></span>
    </div>
    <?php require dirname(__DIR__) . '/partials/social_sign_in.php'; ?>
    <p class="text-center text-sm text-[#64748b]">Đã có tài khoản? <a class="font-bold text-[#152238] underline decoration-[#c2410c] decoration-2 underline-offset-4 transition hover:text-[#c2410c]" href="/login">Đăng nhập</a></p>
</form>
