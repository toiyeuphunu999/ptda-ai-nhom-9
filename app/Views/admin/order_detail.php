<?php
use App\Core\Csrf;
use App\Helpers\ViewHelper;

$status = (string)$order['status'];
$next = ['pending' => 'confirmed', 'confirmed' => 'shipping', 'shipping' => 'delivered'];
$statusTone = [
    'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'icon' => 'schedule'],
    'confirmed' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'icon' => 'verified'],
    'shipping' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'icon' => 'local_shipping'],
    'delivered' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'icon' => 'task_alt'],
    'cancelled' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'icon' => 'cancel'],
    'cancel_requested' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'icon' => 'pending_actions'],
    'return_requested' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'icon' => 'assignment_return'],
    'return_shipping' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'icon' => 'local_shipping'],
    'returned' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'icon' => 'assignment_turned_in'],
];
$tone = $statusTone[$status] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'icon' => 'info'];
$panel = 'rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60';
$input = 'mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:bg-slate-100';
$button = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-60';
?>

<div class="mx-auto max-w-[1440px] space-y-6">
    <header class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.18em] text-orange-600">Bán hàng</p>
            <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Chi tiết đơn hàng</h1>
            <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-500">
                <span class="font-semibold text-slate-700">#<?= ViewHelper::escape((string)$order['order_code']) ?></span><span aria-hidden="true">·</span><span><?= ViewHelper::escape((string)$order['customer_name']) ?></span><span aria-hidden="true">·</span><span class="break-all"><?= ViewHelper::escape((string)$order['customer_email']) ?></span>
            </div>
        </div>
        <a href="/admin/orders" class="<?= $button ?> shrink-0 border border-slate-300 bg-white text-slate-700 hover:border-slate-400 hover:bg-slate-50"><span class="material-symbols-outlined text-[19px]" aria-hidden="true">arrow_back</span>Quay lại</a>
    </header>

    <?php if (!empty($error)): ?>
        <div role="alert" class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800"><span class="material-symbols-outlined mt-0.5 text-[20px]" aria-hidden="true">error</span><span><?= ViewHelper::escape($error) ?></span></div>
    <?php endif; ?>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,.75fr)]">
        <main class="min-w-0 space-y-6">
            <section class="<?= $panel ?> overflow-hidden">
                <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex items-center gap-3"><span class="flex size-11 shrink-0 items-center justify-center rounded-xl <?= $tone['bg'] ?> <?= $tone['text'] ?>"><span class="material-symbols-outlined" aria-hidden="true"><?= $tone['icon'] ?></span></span><div><p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Trạng thái đơn</p><h2 class="mt-1 text-lg font-bold text-slate-950"><?= ViewHelper::escape(ViewHelper::orderStatus($status)) ?></h2></div></div>
                    <time class="inline-flex w-fit rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-600"><?= ViewHelper::escape((string)$order['created_at']) ?></time>
                </div>

                <div class="space-y-5 px-5 py-5 sm:px-6">
                    <?php if (isset($next[$status])): ?>
                        <form method="post" action="/admin/orders/<?= (int)$order['id'] ?>/status" class="rounded-xl border border-blue-100 bg-blue-50/60 p-4">
                            <?= Csrf::field() ?><input type="hidden" name="status" value="<?= $next[$status] ?>">
                            <label for="status-reason" class="text-sm font-semibold text-slate-800">Ghi chú cập nhật</label><textarea id="status-reason" name="reason" rows="2" required class="<?= $input ?>" placeholder="Nhập ghi chú cho lần cập nhật này..."></textarea>
                            <button type="submit" class="<?= $button ?> mt-3 bg-blue-700 text-white hover:bg-blue-800">Chuyển sang <?= ViewHelper::escape(ViewHelper::orderStatus($next[$status])) ?><span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span></button>
                        </form>
                        <form method="post" action="/admin/orders/<?= (int)$order['id'] ?>/status" class="rounded-xl border border-red-100 bg-red-50/60 p-4">
                            <?= Csrf::field() ?><input type="hidden" name="status" value="cancelled">
                            <label for="admin-cancel-reason" class="text-sm font-semibold text-slate-800">Lý do hủy đơn</label><textarea id="admin-cancel-reason" name="reason" rows="2" required class="<?= $input ?>" placeholder="Nhập lý do hủy đơn..."></textarea>
                            <button type="submit" class="<?= $button ?> mt-3 bg-red-700 text-white hover:bg-red-800"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">cancel</span>Hủy đơn</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($status === 'cancel_requested'): ?>
                        <form method="post" action="/admin/orders/<?= (int)$order['id'] ?>/cancel-decision" class="rounded-xl border border-orange-100 bg-orange-50/60 p-4">
                            <?= Csrf::field() ?><div class="mb-4 flex items-start gap-3 text-sm text-orange-900"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">help</span><p>Khách hàng đang yêu cầu hủy đơn. Hãy ghi chú trước khi xử lý.</p></div>
                            <label for="cancel-decision-reason" class="text-sm font-semibold text-slate-800">Lý do xử lý yêu cầu hủy</label><textarea id="cancel-decision-reason" name="reason" rows="2" required class="<?= $input ?>"></textarea>
                            <div class="mt-3 flex flex-wrap gap-2"><button name="decision" value="approve" type="submit" class="<?= $button ?> bg-red-700 text-white hover:bg-red-800">Chấp nhận hủy</button><button name="decision" value="reject" type="submit" class="<?= $button ?> border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">Từ chối hủy</button></div>
                        </form>
                    <?php endif; ?>

                    <?php if ($status === 'return_requested'): ?>
                        <form method="post" action="/admin/orders/<?= (int)$order['id'] ?>/return-decision" class="rounded-xl border border-orange-100 bg-orange-50/60 p-4">
                            <?= Csrf::field() ?><p class="text-sm text-slate-700"><span class="font-semibold text-slate-900">Lý do khách:</span> <?= ViewHelper::escape((string)$order['return_reason']) ?></p>
                            <?php if (!empty($order['return_image'])): ?><a href="/admin/orders/<?= (int)$order['id'] ?>/return-image" target="_blank" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-blue-700 hover:text-blue-800"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">open_in_new</span>Xem hình ảnh đổi trả</a><?php endif; ?>
                            <label for="return-decision-reason" class="mt-4 block text-sm font-semibold text-slate-800">Ghi chú xử lý</label><textarea id="return-decision-reason" name="reason" rows="2" required class="<?= $input ?>"></textarea>
                            <div class="mt-3 flex flex-wrap gap-2"><button name="decision" value="approve" type="submit" class="<?= $button ?> bg-blue-700 text-white hover:bg-blue-800">Đồng ý đổi trả</button><button name="decision" value="reject" type="submit" class="<?= $button ?> border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">Từ chối đổi trả</button></div>
                        </form>
                    <?php endif; ?>

                    <?php if ($status === 'return_shipping'): ?>
                        <form method="post" action="/admin/orders/<?= (int)$order['id'] ?>/return-received" class="rounded-xl border border-purple-100 bg-purple-50/60 p-4">
                            <?= Csrf::field() ?><label for="return-received-reason" class="text-sm font-semibold text-slate-800">Ghi chú nhận hàng trả</label><textarea id="return-received-reason" name="reason" rows="2" required class="<?= $input ?>"></textarea>
                            <button type="submit" class="<?= $button ?> mt-3 bg-purple-700 text-white hover:bg-purple-800">Xác nhận đã nhận hàng trả</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($status === 'cancelled' && !empty($order['cancel_reason'])): ?><div class="flex items-start gap-3 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3.5 text-sm text-orange-900"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">info</span><p><span class="font-semibold">Lý do hủy:</span> <?= ViewHelper::escape((string)$order['cancel_reason']) ?></p></div><?php endif; ?>

                    <div>
                        <div class="mb-4 flex items-end justify-between gap-4"><div><p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Sản phẩm</p><h2 class="mt-1 text-lg font-bold text-slate-950">Sản phẩm trong đơn</h2></div><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"><?= count($order['items']) ?> sản phẩm</span></div>
                        <div class="divide-y divide-slate-100 rounded-xl border border-slate-200">
                            <?php foreach ($order['items'] as $item): ?><div class="flex flex-col gap-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"><div class="min-w-0"><p class="font-medium leading-6 text-slate-800"><?= ViewHelper::escape($item['book_title']) ?></p><p class="mt-1 text-sm text-slate-500">Số lượng: <?= (int)$item['quantity'] ?></p></div><strong class="shrink-0 text-sm font-bold text-slate-950"><?= ViewHelper::money((int)$item['line_total']) ?></strong></div><?php endforeach; ?>
                            <div class="flex items-center justify-between gap-4 bg-slate-50 px-4 py-4"><span class="font-semibold text-slate-700">Tổng tiền</span><strong class="text-xl font-bold text-slate-950"><?= ViewHelper::money((int)$order['total']) ?></strong></div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <aside class="min-w-0 space-y-6">
            <section class="<?= $panel ?> p-5 sm:p-6"><div class="mb-5 flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700"><span class="material-symbols-outlined" aria-hidden="true">local_shipping</span></span><h2 class="text-lg font-bold text-slate-950">Thông tin giao hàng</h2></div><dl class="space-y-4 text-sm"><div><dt class="text-slate-500">Người nhận</dt><dd class="mt-1 font-semibold text-slate-900"><?= ViewHelper::escape($order['shipping_name']) ?></dd></div><div><dt class="text-slate-500">Số điện thoại</dt><dd class="mt-1 text-slate-800"><?= ViewHelper::escape($order['shipping_phone']) ?></dd></div><div><dt class="text-slate-500">Email</dt><dd class="mt-1 break-all text-slate-800"><?= ViewHelper::escape($order['shipping_email']) ?></dd></div><div><dt class="text-slate-500">Địa chỉ</dt><dd class="mt-1 leading-6 text-slate-800"><?= ViewHelper::escape($order['shipping_address']) ?></dd></div></dl></section>

            <section class="<?= $panel ?> p-5 sm:p-6"><div class="mb-5 flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"><span class="material-symbols-outlined" aria-hidden="true">payments</span></span><h2 class="text-lg font-bold text-slate-950">Thanh toán</h2></div><dl class="space-y-4 text-sm"><div><dt class="text-slate-500">Phương thức</dt><dd class="mt-1 font-semibold text-slate-900"><?= ViewHelper::escape(ViewHelper::paymentMethod((string)$order['payment_method'])) ?></dd></div><div><dt class="text-slate-500">Trạng thái</dt><dd class="mt-1 font-semibold text-slate-900"><?= ViewHelper::escape(ViewHelper::paymentStatus((string)$order['payment_status'])) ?></dd></div></dl></section>

            <section class="<?= $panel ?> p-5 sm:p-6"><div class="mb-5 flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700"><span class="material-symbols-outlined" aria-hidden="true">history</span></span><h2 class="text-lg font-bold text-slate-950">Lịch sử đơn hàng</h2></div><ol class="relative space-y-5 border-l border-slate-200 pl-5"><?php foreach ($order['history'] as $history): ?><li class="relative"><span class="absolute -left-[25px] top-0.5 flex size-3 items-center justify-center rounded-full border-2 border-white bg-blue-600 ring-1 ring-blue-200"></span><p class="text-sm font-semibold text-slate-900"><?= ViewHelper::escape(ViewHelper::orderStatus((string)$history['to_status'])) ?></p><p class="mt-1 text-sm leading-6 text-slate-600"><?= ViewHelper::escape((string)$history['note']) ?></p><time class="mt-1 block text-xs text-slate-400"><?= ViewHelper::escape((string)$history['created_at']) ?></time></li><?php endforeach; ?></ol></section>
        </aside>
    </div>
</div>