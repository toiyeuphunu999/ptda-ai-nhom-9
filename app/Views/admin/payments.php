<?php
use App\Helpers\ViewHelper;

$payments = $payments ?? [];
$method = $method ?? null;
$status = $status ?? null;
$paymentMethods = ['cod'=>'COD','bank_transfer'=>'PayOS / VietQR','momo'=>'MoMo','vnpay'=>'VNPay','zalopay'=>'ZaloPay'];
$paymentStatuses = ['pending'=>'Chờ thanh toán','processing'=>'Đang xử lý','paid'=>'Đã thanh toán','failed'=>'Thất bại','cancelled'=>'Đã hủy','refunded'=>'Đã hoàn tiền'];
?>
<div class="section-heading report-heading">
    <div>
        <p class="eyebrow">Đối soát</p>
        <h2>Quản lý thanh toán</h2>
        <p class="muted">Theo dõi giao dịch COD và PayOS được ghi nhận từ đơn hàng.</p>
    </div>
    <a class="button" href="/admin/reports">Báo cáo tổng hợp</a>
</div>

<form class="admin-filter" method="get" action="/admin/payments">
    <label>Phương thức
        <select name="method">
            <option value="">Tất cả phương thức</option>
            <?php foreach ($paymentMethods as $value => $label): ?><option value="<?= $value ?>" <?= $method === $value ? 'selected' : '' ?>><?= ViewHelper::escape($label) ?></option><?php endforeach; ?>
        </select>
    </label>
    <label>Trạng thái
        <select name="status">
            <option value="">Tất cả trạng thái</option>
            <?php foreach ($paymentStatuses as $value => $label): ?><option value="<?= $value ?>" <?= $status === $value ? 'selected' : '' ?>><?= ViewHelper::escape($label) ?></option><?php endforeach; ?>
        </select>
    </label>
    <button class="button button-primary" type="submit">Lọc giao dịch</button>
    <?php if ($method || $status): ?><a class="button" href="/admin/payments">Xóa bộ lọc</a><?php endif; ?>
</form>

<section class="admin-panel payment-ledger-panel">
    <div class="section-heading"><div><p class="eyebrow">Sổ giao dịch</p><h3><?= count($payments) ?> giao dịch</h3></div></div>
    <?php if (empty($payments)): ?><p class="muted">Chưa có giao dịch phù hợp với bộ lọc.</p><?php else: ?>
    <div class="table-wrap"><table class="admin-table payment-ledger">
        <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Phương thức</th><th>Số tiền</th><th>Trạng thái</th><th>Mã giao dịch</th><th>Thời gian</th></tr></thead>
        <tbody><?php foreach ($payments as $payment): ?>
            <tr>
                <td><strong><?= ViewHelper::escape($payment['order_code']) ?></strong><br><small>Đơn: <?= ViewHelper::escape(ViewHelper::orderStatus((string)$payment['order_status'])) ?></small></td>
                <td><?= ViewHelper::escape($payment['customer_name']) ?><br><small><?= ViewHelper::escape($payment['customer_email']) ?></small></td>
                <td><?= ViewHelper::escape($paymentMethods[$payment['method']] ?? $payment['method']) ?><br><small><?= ViewHelper::escape($payment['provider'] ?: 'Nội bộ') ?></small></td>
                <td><strong><?= ViewHelper::money((int)$payment['amount']) ?></strong></td>
                <td><span class="report-badge <?= $payment['status'] === 'paid' ? 'report-badge-paid' : 'report-badge-neutral' ?>"><?= ViewHelper::escape($paymentStatuses[$payment['status']] ?? $payment['status']) ?></span></td>
                <td><small><?= ViewHelper::escape($payment['transaction_code'] ?: 'Chưa có') ?></small></td>
                <td><small><?= ViewHelper::escape($payment['created_at']) ?></small><?php if (!empty($payment['paid_at'])): ?><br><small>Thanh toán: <?= ViewHelper::escape($payment['paid_at']) ?></small><?php endif; ?></td>
            </tr>
        <?php endforeach; ?></tbody>
    </table></div>
    <?php endif; ?>
</section>
