<?php $activeInfoPage = 'returns'; $infoEyebrow = 'Chính sách mua sắm'; $infoHeading = 'Đổi trả & hoàn tiền'; $infoLead = 'Các bước cần biết khi sản phẩm bị lỗi, giao sai hoặc đơn hàng cần được hỗ trợ sau khi nhận.'; require dirname(__DIR__) . '/partials/info_header.php'; ?>
<section class="container info-layout">
    <?php require dirname(__DIR__) . '/partials/info_nav.php'; ?>
    <div class="info-content">
        <p class="policy-meta">Cập nhật lần cuối: <?= date('d/m/Y') ?></p>
        <div class="info-section" id="dieu-kien"><h2>Điều kiện đổi trả</h2><p>Mộc Bản tiếp nhận yêu cầu khi sách bị hư hỏng do vận chuyển, giao sai sản phẩm hoặc sản phẩm không đúng với đơn hàng đã xác nhận.</p><p>Sách cần còn đủ phụ kiện, quà tặng đi kèm nếu có và không có dấu hiệu đã qua sử dụng ngoài lỗi cần phản ánh.</p></div>
        <div class="info-section" id="thoi-han"><h2>Thời hạn phản hồi</h2><p>Vui lòng liên hệ ngay khi phát hiện vấn đề, tốt nhất trong vòng 48 giờ kể từ lúc nhận hàng. Hãy gửi kèm mã đơn và hình ảnh tình trạng sản phẩm để được hỗ trợ nhanh hơn.</p></div>
        <div class="info-section" id="xu-ly"><h2>Cách xử lý</h2><ol><li>Gửi yêu cầu qua trang Liên hệ.</li><li>Nhà sách kiểm tra thông tin đơn và tình trạng sản phẩm.</li><li>Hai bên thống nhất phương án đổi sản phẩm hoặc hoàn tiền nếu đủ điều kiện.</li></ol></div>
        <div class="info-section" id="khong-ap-dung"><h2>Trường hợp không áp dụng</h2><p>Yêu cầu có thể không được chấp nhận nếu sản phẩm hư hỏng do bảo quản hoặc sử dụng không đúng cách, thiếu bằng chứng cần thiết hoặc đã quá thời hạn hỗ trợ.</p><p class="info-note">Nội dung này là chính sách vận hành của đồ án và cần được rà soát lại trước khi áp dụng cho hoạt động kinh doanh thực tế.</p></div>
    </div>
</section>
