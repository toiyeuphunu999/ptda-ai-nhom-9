<?php $activeInfoPage = 'shipping'; $infoEyebrow = 'Chính sách mua sắm'; $infoHeading = 'Vận chuyển'; $infoLead = 'Thông tin về phí vận chuyển, thời gian giao hàng và cách xử lý khi kiện hàng có bất thường.'; require dirname(__DIR__) . '/partials/info_header.php'; ?>
<section class="container info-layout">
    <?php require dirname(__DIR__) . '/partials/info_nav.php'; ?>
    <div class="info-content">
        <p class="policy-meta">Cập nhật lần cuối: <?= date('d/m/Y') ?></p>
        <div class="info-section" id="phi-van-chuyen"><h2>Phí vận chuyển</h2><p>Phí vận chuyển được hiển thị tại bước thanh toán và có thể thay đổi theo khu vực, giá trị đơn hàng hoặc chương trình vận chuyển đang áp dụng.</p><p>Website hiện hiển thị ưu đãi miễn phí vận chuyển từ 250.000₫ khi điều kiện của chương trình được đáp ứng.</p></div>
        <div class="info-section" id="thoi-gian"><h2>Thời gian xử lý và giao hàng</h2><p>Đơn hàng được tiếp nhận sau khi thông tin đặt hàng được ghi nhận. Thời gian giao thực tế phụ thuộc vào địa chỉ nhận hàng, đơn vị vận chuyển, ngày nghỉ và tình trạng sản phẩm.</p><p>Nếu đơn hàng có dấu hiệu chậm hơn dự kiến, vui lòng liên hệ và cung cấp mã đơn để được kiểm tra.</p></div>
        <div class="info-section" id="kiem-tra"><h2>Kiểm tra khi nhận hàng</h2><p>Bạn nên kiểm tra số lượng, tên sách và tình trạng bề ngoài của kiện hàng khi nhận. Nếu có bất thường, hãy ghi nhận với đơn vị giao hàng và liên hệ Mộc Bản sớm nhất.</p></div>
        <div class="info-section" id="ho-tro"><h2>Cần hỗ trợ vận chuyển?</h2><p>Gửi mã đơn, số điện thoại đặt hàng và mô tả vấn đề qua trang Liên hệ. Mộc Bản sẽ kiểm tra theo thông tin đơn hàng.</p><a class="button button-primary" href="/contact">Liên hệ hỗ trợ</a></div>
    </div>
</section>
