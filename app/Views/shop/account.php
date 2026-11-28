<?php

use App\Core\Csrf;
use App\Helpers\ViewHelper;

$card = 'rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60';
$field = 'mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:bg-slate-100';
$button = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-60';
?>

<div class="mx-auto max-w-[1240px] px-4 pb-16 pt-8 sm:px-6 lg:px-8">
    <?php require dirname(__DIR__) . '/partials/breadcrumbs.php'; ?>

    <header class="mt-6 flex flex-col gap-5 border-b border-slate-200 pb-7 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-orange-600">Tài khoản</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Xin chào, <?= ViewHelper::escape((string)($user['name'] ?? 'bạn')) ?></h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Quản lý thông tin cá nhân, địa chỉ nhận hàng và theo dõi quyền lợi thành viên của bạn.</p>
        </div>
        <a href="/orders" class="<?= $button ?> shrink-0 bg-blue-900 text-white hover:bg-blue-950"><span class="material-symbols-outlined text-[19px]" aria-hidden="true">receipt_long</span>Lịch sử đơn hàng</a>
    </header>

    <section class="mt-7 grid gap-5 lg:grid-cols-[minmax(0,1.35fr)_minmax(300px,.65fr)]">
        <article class="<?= $card ?> overflow-hidden">
            <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-5 sm:px-6">
                <span class="flex size-11 items-center justify-center rounded-xl bg-blue-50 text-blue-800"><span class="material-symbols-outlined" aria-hidden="true">person</span></span>
                <div><p class="text-[11px] font-bold uppercase tracking-[0.15em] text-slate-500">Hồ sơ</p><h2 class="mt-1 text-xl font-bold text-slate-950">Thông tin cá nhân</h2></div>
            </div>
            <div class="px-5 py-5 sm:px-6">
                <div class="grid gap-3 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-2">
                    <div><p class="text-xs text-slate-500">Email</p><p class="mt-1 break-all font-semibold text-slate-900"><?= ViewHelper::escape((string)($user['email'] ?? '')) ?></p></div>
                    <div><p class="text-xs text-slate-500">Vai trò</p><p class="mt-1 font-semibold text-slate-900"><?= ViewHelper::escape(ViewHelper::userRole((string)($user['role'] ?? 'customer'))) ?></p></div>
                    <div class="sm:col-span-2"><p class="text-xs text-slate-500">Mã giới thiệu</p><p class="mt-1 font-semibold text-slate-900"><?= ViewHelper::escape($user['referral_code'] ?? 'Chưa có') ?></p></div>
                </div>

                <?php if (!empty($profileError)): ?><div role="alert" class="mt-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">error</span><span><?= ViewHelper::escape($profileError) ?></span></div><?php endif; ?>

                <form id="account-profile-form" class="mt-6 grid gap-5 sm:grid-cols-2" method="post" action="/account">
                    <?= Csrf::field() ?>
                    <label for="account-name" class="text-sm font-semibold text-slate-800">Họ và tên<input class="<?= $field ?>" id="account-name" name="name" type="text" maxlength="120" value="<?= ViewHelper::escape((string)($formName ?? $user['name'] ?? '')) ?>" required></label>
                    <label for="account-phone" class="text-sm font-semibold text-slate-800">Số điện thoại nhận hàng<input class="<?= $field ?>" id="account-phone" name="phone" type="tel" maxlength="30" autocomplete="tel" value="<?= ViewHelper::escape((string)($formPhone ?? $user['phone'] ?? '')) ?>" placeholder="0901 234 567"></label>
                    <label for="account-address" class="text-sm font-semibold text-slate-800 sm:col-span-2">Địa chỉ nhận hàng mặc định<textarea class="<?= $field ?> min-h-28 resize-y" id="account-address" name="address" rows="3" maxlength="255" autocomplete="street-address" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"><?= ViewHelper::escape((string)($formAddress ?? $user['address'] ?? '')) ?></textarea><span class="mt-1 block text-xs font-normal leading-5 text-slate-500">Thông tin sẽ được tự điền ở bước thanh toán và vẫn có thể chỉnh lại cho từng đơn hàng.</span></label>
                </form>
                <form id="account-logout-form" class="hidden" method="post" action="/logout"><?= Csrf::field() ?></form>
                <div class="mt-5 flex flex-wrap gap-3"><button type="submit" form="account-profile-form" class="<?= $button ?> bg-orange-700 text-white hover:bg-orange-800"><span class="material-symbols-outlined text-[19px]" aria-hidden="true">save</span>Lưu thay đổi</button><button type="submit" form="account-logout-form" class="<?= $button ?> border border-slate-300 bg-white text-slate-700 hover:bg-slate-50"><span class="material-symbols-outlined text-[19px]" aria-hidden="true">logout</span>Đăng xuất</button></div>
            </div>
        </article>

        <aside class="space-y-5">
            <article class="relative overflow-hidden rounded-2xl bg-blue-950 p-6 text-white shadow-lg shadow-blue-950/15">
                <div class="absolute -right-10 -top-10 size-36 rounded-full bg-blue-800/50"></div><div class="relative"><div class="flex items-center justify-between gap-3"><div><p class="text-[11px] font-bold uppercase tracking-[0.15em] text-blue-200">Thành viên</p><h2 class="mt-2 text-xl font-bold">Điểm thưởng</h2></div><span class="flex size-11 items-center justify-center rounded-xl bg-white/10 text-orange-300"><span class="material-symbols-outlined" aria-hidden="true">stars</span></span></div><p class="mt-8 text-4xl font-bold tracking-tight"><?= (int)($pointsBalance ?? 0) ?><span class="ml-2 text-base font-medium text-blue-200">điểm</span></p><p class="mt-3 text-sm leading-6 text-blue-100">Điểm được cộng khi hoàn tất đơn hàng, đánh giá sách và giới thiệu thành viên mới.</p><a href="#points-history" class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-white underline decoration-blue-300 underline-offset-4 hover:text-orange-200">Xem biến động điểm<span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_downward</span></a></div>
            </article>
            <a href="/orders" class="group flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 text-slate-900 shadow-sm shadow-slate-200/60 transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"><span class="flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-xl bg-orange-50 text-orange-700"><span class="material-symbols-outlined" aria-hidden="true">local_shipping</span></span><span><span class="block text-sm font-bold">Theo dõi đơn hàng</span><span class="mt-1 block text-xs text-slate-500">Xem trạng thái các đơn đã đặt</span></span></span><span class="material-symbols-outlined text-slate-400 transition group-hover:translate-x-1 group-hover:text-blue-700" aria-hidden="true">arrow_forward</span></a>
        </aside>
    </section>

    <section class="mt-8">
        <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-[11px] font-bold uppercase tracking-[0.16em] text-orange-600">Giao hàng</p><h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-950">Địa chỉ nhận hàng</h2></div><p class="text-sm text-slate-500">Lưu địa chỉ để thanh toán nhanh hơn.</p></div>
        <div class="grid gap-5 xl:grid-cols-2">
            <?php foreach (($addresses ?? []) as $savedAddress): ?>
                <article class="<?= $card ?> flex min-w-0 flex-col p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-4"><div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="text-lg font-bold text-slate-950"><?= ViewHelper::escape($savedAddress['label']) ?></h3><?php if ((int)$savedAddress['is_default'] === 1): ?><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">Mặc định</span><?php endif; ?></div><p class="mt-3 font-semibold text-slate-800"><?= ViewHelper::escape($savedAddress['recipient_name']) ?> <span class="font-normal text-slate-400">·</span> <?= ViewHelper::escape($savedAddress['phone']) ?></p><p class="mt-1 text-sm leading-6 text-slate-600"><?= ViewHelper::escape($savedAddress['address']) ?></p></div><span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600"><span class="material-symbols-outlined" aria-hidden="true">location_on</span></span></div>
                    <details class="mt-5 border-t border-slate-200 pt-4"><summary class="cursor-pointer text-sm font-semibold text-blue-800 hover:text-blue-950">Chỉnh sửa địa chỉ</summary><form class="mt-4 grid gap-4 sm:grid-cols-2" method="post" action="/account/addresses/<?= (int)$savedAddress['id'] ?>/update"><?= Csrf::field() ?><label class="text-sm font-semibold text-slate-800">Tên địa chỉ<input class="<?= $field ?>" name="label" maxlength="50" value="<?= ViewHelper::escape($savedAddress['label']) ?>" required></label><label class="text-sm font-semibold text-slate-800">Người nhận<input class="<?= $field ?>" name="recipient_name" maxlength="120" value="<?= ViewHelper::escape($savedAddress['recipient_name']) ?>" required></label><label class="text-sm font-semibold text-slate-800">Số điện thoại<input class="<?= $field ?>" name="phone" maxlength="30" value="<?= ViewHelper::escape($savedAddress['phone']) ?>" required></label><label class="text-sm font-semibold text-slate-800 sm:col-span-2">Địa chỉ<textarea class="<?= $field ?> min-h-24 resize-y" name="address" rows="3" maxlength="255" required><?= ViewHelper::escape($savedAddress['address']) ?></textarea></label><button type="submit" class="<?= $button ?> w-fit bg-orange-700 text-white hover:bg-orange-800">Lưu thay đổi</button></form></details>
                    <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4"><?php if ((int)$savedAddress['is_default'] !== 1): ?><form method="post" action="/account/addresses/<?= (int)$savedAddress['id'] ?>/default"><?= Csrf::field() ?><button type="submit" class="<?= $button ?> min-h-10 border border-slate-300 bg-white px-3.5 py-2 text-xs text-slate-700 hover:bg-slate-50"><span class="material-symbols-outlined text-[17px]" aria-hidden="true">check_circle</span>Đặt mặc định</button></form><?php endif; ?><form method="post" action="/account/addresses/<?= (int)$savedAddress['id'] ?>/delete" onsubmit="return confirm('Xóa địa chỉ này?');"><?= Csrf::field() ?><button type="submit" class="<?= $button ?> min-h-10 border border-red-200 bg-red-50 px-3.5 py-2 text-xs text-red-700 hover:bg-red-100"><span class="material-symbols-outlined text-[17px]" aria-hidden="true">delete</span>Xóa</button></form></div>
                </article>
            <?php endforeach; ?>

            <form class="<?= $card ?> p-5 sm:p-6" method="post" action="/account/addresses"><div class="mb-5 flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-xl bg-orange-50 text-orange-700"><span class="material-symbols-outlined" aria-hidden="true">add_location_alt</span></span><div><p class="text-[11px] font-bold uppercase tracking-[0.15em] text-slate-500">Địa chỉ mới</p><h3 class="mt-1 text-lg font-bold text-slate-950">Thêm địa chỉ nhận hàng</h3></div></div><?= Csrf::field() ?><div class="grid gap-4 sm:grid-cols-2"><label class="text-sm font-semibold text-slate-800">Tên địa chỉ<input class="<?= $field ?>" id="address-label" name="label" maxlength="50" placeholder="Nhà riêng, Công ty..." required></label><label class="text-sm font-semibold text-slate-800">Người nhận<input class="<?= $field ?>" id="address-recipient" name="recipient_name" maxlength="120" value="<?= ViewHelper::escape($user['name'] ?? '') ?>" required></label><label class="text-sm font-semibold text-slate-800">Số điện thoại<input class="<?= $field ?>" id="address-phone" name="phone" maxlength="30" value="<?= ViewHelper::escape($user['phone'] ?? '') ?>" required></label><label class="text-sm font-semibold text-slate-800 sm:col-span-2">Địa chỉ<textarea class="<?= $field ?> min-h-24 resize-y" id="address-value" name="address" rows="3" maxlength="255" required></textarea></label></div><label class="mt-4 flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-700"><input class="size-4 accent-blue-800" type="checkbox" name="is_default" value="1">Đặt làm địa chỉ mặc định</label><button type="submit" class="<?= $button ?> mt-5 w-fit bg-orange-700 text-white hover:bg-orange-800"><span class="material-symbols-outlined text-[19px]" aria-hidden="true">add</span>Thêm địa chỉ</button></form>
        </div>
    </section>

    <section id="points-history" class="mt-10 scroll-mt-6">
        <div class="mb-5"><p class="text-[11px] font-bold uppercase tracking-[0.16em] text-orange-600">Lịch sử</p><h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-950">Biến động điểm thưởng</h2></div>
        <?php if (empty($pointsHistory)): ?>
            <div class="<?= $card ?> flex items-center gap-3 p-6 text-sm text-slate-600"><span class="material-symbols-outlined text-[22px] text-slate-400" aria-hidden="true">history</span>Bạn chưa có giao dịch điểm thưởng.</div>
        <?php else: ?>
            <div class="<?= $card ?> overflow-hidden"><div class="overflow-x-auto"><table class="w-full min-w-[620px] border-collapse text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500"><tr><th class="px-5 py-4 font-bold">Nội dung</th><th class="px-5 py-4 font-bold">Điểm</th><th class="px-5 py-4 font-bold">Thời gian</th></tr></thead><tbody class="divide-y divide-slate-100"><?php foreach ($pointsHistory as $transaction): ?><tr class="text-slate-700"><td class="px-5 py-4 font-medium"><?= ViewHelper::escape($transaction['description']) ?></td><td class="px-5 py-4 font-bold <?= (int)$transaction['points'] > 0 ? 'text-emerald-700' : 'text-red-700' ?>"><?= (int)$transaction['points'] > 0 ? '+' : '' ?><?= (int)$transaction['points'] ?></td><td class="px-5 py-4 text-slate-500"><?= ViewHelper::escape($transaction['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
        <?php endif; ?>
    </section>
</div>
