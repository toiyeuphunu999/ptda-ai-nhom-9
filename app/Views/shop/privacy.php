<?php $activeInfoPage = 'privacy'; $infoEyebrow = 'Thông tin pháp lý'; $infoHeading = 'Chính sách bảo mật'; $infoLead = 'Cách Mộc Bản sử dụng và bảo vệ thông tin cần thiết cho tài khoản, đơn hàng và hỗ trợ khách hàng.'; require dirname(__DIR__) . '/partials/info_header.php'; ?>
<section class="container info-layout">
    <?php require dirname(__DIR__) . '/partials/info_nav.php'; ?>
    <div class="info-content">
        <p class="policy-meta">Cập nhật lần cuối: <?= date('d/m/Y') ?></p>
        <div class="info-section" id="du-lieu"><h2>1. Dữ liệu được thu thập</h2><p>Mộc Bản có thể cần họ tên, email, số điện thoại, địa chỉ nhận hàng và thông tin đơn hàng để tạo tài khoản, xử lý giao hàng và hỗ trợ sau mua.</p></div>
        <div class="info-section" id="su-dung"><h2>2. Cách sử dụng dữ liệu</h2><p>Dữ liệu được dùng để xác thực tài khoản, xử lý đơn, cập nhật trạng thái, hỗ trợ khách hàng và cải thiện trải nghiệm sử dụng website.</p><p>Mật khẩu được lưu dưới dạng hash; thông tin nhạy cảm của các kết nối hệ thống được bảo vệ theo cấu hình ứng dụng.</p></div>
        <div class="info-section" id="chia-se"><h2>3. Chia sẻ dữ liệu</h2><p>Mộc Bản chỉ chia sẻ thông tin cần thiết với đơn vị vận chuyển hoặc nhà cung cấp thanh toán khi việc đó phục vụ trực tiếp cho đơn hàng. Mộc Bản không bán thông tin cá nhân của khách hàng.</p></div>
        <div class="info-section" id="quyen"><h2>4. Quyền của khách hàng</h2><p>Bạn có thể yêu cầu kiểm tra, cập nhật hoặc hỗ trợ đối với thông tin tài khoản của mình bằng cách liên hệ nhà sách. Bạn cũng có thể yêu cầu giải thích về việc sử dụng dữ liệu liên quan đến đơn hàng.</p></div>
    </div>
</section>
