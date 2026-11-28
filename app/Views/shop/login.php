<?php

use App\Core\Csrf;
use App\Helpers\ViewHelper;
?>
<div class="grid gap-5">
    <form class="grid gap-5 rounded-3xl border border-[#e2e8f0] bg-white p-6 shadow-[0_24px_70px_-38px_rgba(21,34,56,.45)] sm:p-9" method="post" action="/login">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.2em] text-[#c2410c]">Tài khoản</p>
            <h1 class="mt-3 text-3xl font-extrabold tracking-[-.04em] text-[#152238] sm:text-4xl">Chào mừng trở lại</h1>
            <p class="mt-3 text-sm leading-6 text-[#64748b]">Đăng nhập để tiếp tục hành trình đọc và quản lý đơn hàng của bạn.</p>
        </div>
        <?php if (!empty($error)): ?>
            <div class="flex items-start gap-3 rounded-2xl border border-[#fed7aa] bg-[#fff7ed] px-4 py-3 text-sm leading-6 text-[#9a3412]" role="alert">
                <span class="mt-0.5 font-bold">!</span>
                <span><?= ViewHelper::escape($error) ?></span>
            </div>
        <?php endif; ?>
        <?= Csrf::field() ?>
        <div class="grid gap-2">
            <label class="text-sm font-semibold text-[#172033]" for="email">Email</label>
            <input class="min-h-12 rounded-xl border border-[#cbd5e1] bg-white px-4 text-base text-[#172033] outline-none transition placeholder:text-[#94a3b8] hover:border-[#94a3b8] focus:border-[#152238] focus:ring-4 focus:ring-[#dbeafe]" id="email" name="email" type="email" value="<?= ViewHelper::escape($email ?? '') ?>" autocomplete="email" placeholder="you@example.com" required>
        </div>
        <div class="grid gap-2">
            <div class="flex items-center justify-between gap-3">
                <label class="text-sm font-semibold text-[#172033]" for="password">Mật khẩu</label>
                <a class="text-xs font-semibold text-[#c2410c] transition hover:text-[#9a3412]" href="/forgot-password">Quên mật khẩu?</a>
            </div>
            <input class="min-h-12 rounded-xl border border-[#cbd5e1] bg-white px-4 text-base text-[#172033] outline-none transition placeholder:text-[#94a3b8] hover:border-[#94a3b8] focus:border-[#152238] focus:ring-4 focus:ring-[#dbeafe]" id="password" name="password" type="password" autocomplete="current-password" placeholder="Nhập mật khẩu của bạn" required>
        </div>
        <button class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#c2410c] px-5 text-sm font-bold text-white shadow-[0_12px_24px_-14px_rgba(194,65,12,.8)] transition hover:-translate-y-0.5 hover:bg-[#9a3412] focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-[#152238] active:translate-y-0" type="submit">Đăng nhập</button>
        <div class="flex items-center gap-3 text-xs font-semibold uppercase tracking-[.14em] text-[#94a3b8]" aria-hidden="true">
            <span class="h-px flex-1 bg-[#e2e8f0]"></span>
            <span>Hoặc tiếp tục với</span>
            <span class="h-px flex-1 bg-[#e2e8f0]"></span>
        </div>
        <?php require dirname(__DIR__) . '/partials/social_sign_in.php'; ?>
        <p class="text-center text-sm text-[#64748b]">Chưa có tài khoản? <a class="font-bold text-[#152238] underline decoration-[#c2410c] decoration-2 underline-offset-4 transition hover:text-[#c2410c]" href="/register">Đăng ký ngay</a></p>
    </form>
    <form class="grid gap-4 rounded-3xl border border-[#dbe4ee] bg-[#edf4fb] p-6 sm:grid-cols-[1fr_auto] sm:items-end sm:p-7" method="post" action="/verify-email/resend">
        <div class="sm:col-span-2">
            <p class="text-sm font-bold text-[#152238]">Chưa nhận được email xác thực?</p>
            <p class="mt-1 text-sm leading-6 text-[#64748b]">Nhập email đăng ký, chúng mình sẽ gửi lại hướng dẫn xác thực.</p>
        </div>
        <?= Csrf::field() ?>
        <div class="grid gap-2"><label class="text-xs font-bold uppercase tracking-[.12em] text-[#64748b]" for="verification-email">Email đăng ký</label><input class="min-h-11 rounded-xl border border-[#cbd5e1] bg-white px-3.5 text-sm outline-none transition hover:border-[#94a3b8] focus:border-[#152238] focus:ring-4 focus:ring-[#dbeafe]" id="verification-email" name="verification_email" type="email" autocomplete="email" required></div>
        <button class="inline-flex min-h-11 items-center justify-center rounded-xl border border-[#152238] bg-white px-4 text-sm font-bold text-[#152238] transition hover:bg-[#152238] hover:text-white focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-[#152238]" type="submit">Gửi lại email</button>
    </form>
</div>
