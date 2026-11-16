<?php
use App\Helpers\ViewHelper;

$reports = $reports ?? [
    'revenue' => 0,
    'orders' => 0,
    'discounts' => 0,
    'payments' => [],
    'daily' => [],
    'books' => [],
    'customers' => [],
    'history' => [],
];
$paymentMethods = [
    'cod' => 'COD',
    'bank_transfer' => 'Chuyển khoản',
    'momo' => 'MoMo',
    'vnpay' => 'VNPay',
    'zalopay' => 'ZaloPay',
];
$paymentStatuses = [
    'pending' => 'Chờ thanh toán',
    'processing' => 'Đang xử lý',
    'paid' => 'Đã thanh toán',
    'failed' => 'Thất bại',
    'cancelled' => 'Đã hủy',
    'refunded' => 'Đã hoàn tiền',
];
?>
<div class="section-heading report-heading">
    <div>
        <p class="eyebrow">Kinh doanh</p>
        <h2>Báo cáo &amp; phân tích</h2>
        <p class="muted">Số liệu được tổng hợp từ đơn hàng, thanh toán và lịch sử khách hàng.</p>
    </div>
    <a class="button" href="/admin">Về tổng quan</a>
</div>

<div class="stats-grid report-stats">
    <div class="stat-card stat-card-highlight"><span>Doanh thu ghi nhận</span><strong><?= ViewHelper::money((int)$reports['revenue']) ?></strong><small>Không tính đơn đã hủy</small></div>
    <div class="stat-card"><span>Đơn có doanh thu</span><strong><?= (int)$reports['orders'] ?></strong><small>Đã xác nhận trở lên</small></div>
    <div class="stat-card"><span>Tổng giảm giá</span><strong><?= ViewHelper::money((int)$reports['discounts']) ?></strong><small>Từ voucher đã dùng</small></div>
    <div class="stat-card"><span>Khách hàng</span><strong><?= count($reports['customers']) ?></strong><small>Có phân hạng thường/VIP</small></div>
</div>

<div class="report-columns">
    <div class="report-column">
        <section class="admin-panel">
        <div class="section-heading"><div><p class="eyebrow">Thanh toán</p><h3>Phương thức &amp; trạng thái</h3></div></div>
        <?php if (empty($reports['payments'])): ?><p class="muted">Chưa có giao dịch thanh toán.</p><?php else: ?>
            <div class="table-wrap"><table class="admin-table report-table"><thead><tr><th>Phương thức</th><th>Trạng thái</th><th>Giao dịch</th><th>Giá trị</th></tr></thead><tbody>
            <?php foreach ($reports['payments'] as $row): ?><tr><td><?= ViewHelper::escape($paymentMethods[$row['payment_method']] ?? $row['payment_method']) ?></td><td><span class="report-badge report-badge-neutral"><?= ViewHelper::escape($paymentStatuses[$row['payment_status']] ?? $row['payment_status']) ?></span></td><td><?= (int)$row['transactions'] ?></td><td><?= ViewHelper::money((int)$row['amount']) ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
        </section>
        <section class="admin-panel">
        <div class="section-heading"><div><p class="eyebrow">Theo dõi</p><h3>Thay đổi trạng thái gần đây</h3></div></div>
        <?php if (empty($reports['history'])): ?><p class="muted">Chưa có lịch sử xử lý đơn hàng.</p><?php else: ?>
            <div class="report-history"><?php foreach ($reports['history'] as $row): ?><div class="report-history-row"><span class="report-history-dot"></span><div><strong><?= ViewHelper::escape($row['order_code']) ?></strong><p><?= ViewHelper::escape(ViewHelper::orderStatus((string)$row['from_status'])) ?> <span class="material-symbols-outlined status-arrow" aria-hidden="true">arrow_forward</span> <?= ViewHelper::escape(ViewHelper::orderStatus((string)$row['to_status'])) ?></p><small><?= ViewHelper::escape($row['changed_by_name'] ?: 'Hệ thống') ?> · <?= ViewHelper::escape($row['created_at']) ?></small></div></div><?php endforeach; ?></div>
        <?php endif; ?>
        </section>
    </div>
    <div class="report-column">
        <section class="admin-panel">
        <div class="section-heading"><div><p class="eyebrow">Doanh thu</p><h3>Doanh thu theo ngày</h3></div></div>
        <?php if (empty($reports['daily'])): ?><p class="muted">Chưa có dữ liệu doanh thu.</p><?php else: ?><div class="table-wrap"><table class="admin-table report-table"><thead><tr><th>Ngày</th><th>Đơn</th><th>Giảm giá</th><th>Doanh thu</th></tr></thead><tbody><?php foreach ($reports['daily'] as $row): ?><tr><td><?= ViewHelper::escape($row['sale_date']) ?></td><td><?= (int)$row['order_count'] ?></td><td><?= ViewHelper::money((int)$row['total_discount']) ?></td><td><?= ViewHelper::money((int)$row['net_revenue']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
        </section>
        <section class="admin-panel">
        <div class="section-heading"><div><p class="eyebrow">Sản phẩm</p><h3>Sách bán chạy</h3></div></div>
        <?php if (empty($reports['books'])): ?><p class="muted">Chưa có dữ liệu bán sách.</p><?php else: ?><div class="table-wrap"><table class="admin-table report-table"><thead><tr><th>Sách</th><th>Danh mục</th><th>Đã bán</th><th>Doanh thu</th></tr></thead><tbody><?php foreach ($reports['books'] as $row): ?><tr><td><strong><?= ViewHelper::escape($row['book_title']) ?></strong><br><small><?= ViewHelper::escape($row['author']) ?></small></td><td><?= ViewHelper::escape($row['category_name']) ?></td><td><?= (int)$row['units_sold'] ?></td><td><?= ViewHelper::money((int)$row['revenue']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
        </section>
    </div>
</div>

<section class="admin-panel report-customer-panel">
    <div class="section-heading"><div><p class="eyebrow">Khách hàng</p><h3>Phân tích khách thường &amp; VIP</h3></div><a href="/admin/customers">Quản lý tài khoản</a></div>
    <?php if (empty($reports['customers'])): ?><p class="muted">Chưa có dữ liệu khách hàng.</p><?php else: ?><div class="table-wrap"><table class="admin-table report-table"><thead><tr><th>Khách hàng</th><th>Hạng</th><th>Số đơn</th><th>Tổng chi tiêu</th><th>Mua gần nhất</th></tr></thead><tbody><?php foreach ($reports['customers'] as $row): ?><tr><td><strong><?= ViewHelper::escape($row['name']) ?></strong><br><small><?= ViewHelper::escape($row['email']) ?></small></td><td><span class="report-badge <?= $row['customer_type'] === 'vip' ? 'report-badge-vip' : 'report-badge-regular' ?>"><?= ViewHelper::escape($row['customer_tier']) ?></span></td><td><?= (int)$row['order_count'] ?></td><td><?= ViewHelper::money((int)$row['total_spent']) ?></td><td><?= ViewHelper::escape($row['last_order_at'] ?: 'Chưa có đơn') ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
