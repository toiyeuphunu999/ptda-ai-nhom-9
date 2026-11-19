<?php $activeInfoPage = 'guide'; $infoEyebrow = 'Hỗ trợ khách hàng'; $infoHeading = 'Hướng dẫn đặt hàng'; $infoLead = 'Từ lúc chọn sách đến khi theo dõi đơn hàng, mọi bước đều được trình bày rõ ràng.'; require dirname(__DIR__) . '/partials/info_header.php'; ?>
<section class="container info-layout">
    <?php require dirname(__DIR__) . '/partials/info_nav.php'; ?>
    <div class="info-content">
        <div class="info-section" id="chon-sach"><h2>1. Chọn sách</h2><p>Duyệt danh mục, tìm theo tên sách hoặc tác giả, sau đó mở trang chi tiết để xem mô tả, giá bán, đánh giá và số lượng tồn kho.</p></div>
        <div class="info-section" id="gio-hang"><h2>2. Thêm vào giỏ hàng</h2><p>Chọn số lượng phù hợp rồi bấm thêm vào giỏ. Bạn có thể cập nhật số lượng hoặc xóa sản phẩm trước khi thanh toán.</p></div>
        <div class="info-section" id="dat-hang"><h2>3. Nhập thông tin nhận hàng</h2><p>Kiểm tra họ tên, số điện thoại và địa chỉ nhận hàng. Thông tin chính xác giúp đơn hàng được xử lý thuận lợi.</p></div>
        <div class="info-section" id="thanh-toan"><h2>4. Chọn thanh toán</h2><p>Chọn COD nếu muốn thanh toán khi nhận hàng. Nếu có lựa chọn thanh toán online, hãy hoàn tất giao dịch theo hướng dẫn và không đóng trang quá sớm.</p></div>
        <div class="info-section" id="theo-doi"><h2>5. Theo dõi đơn hàng</h2><p>Đăng nhập và mở Lịch sử đơn hàng để xem trạng thái. Khi cần hỗ trợ, gửi mã đơn cùng nội dung cần kiểm tra qua trang Liên hệ.</p><div class="info-actions"><a class="button button-primary" href="/books">Bắt đầu chọn sách</a><a class="button" href="/orders">Xem đơn hàng</a></div></div>
    </div>
</section>
