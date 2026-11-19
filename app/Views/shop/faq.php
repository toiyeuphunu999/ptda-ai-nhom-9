<?php $activeInfoPage = 'faq'; $infoEyebrow = 'Hỗ trợ khách hàng'; $infoHeading = 'Câu hỏi thường gặp'; $infoLead = 'Những câu trả lời ngắn gọn cho các bước chọn sách, đặt hàng, thanh toán và quản lý tài khoản.'; require dirname(__DIR__) . '/partials/info_header.php'; ?>
<section class="container info-layout">
    <?php require dirname(__DIR__) . '/partials/info_nav.php'; ?>
    <div class="info-content">
        <div class="info-section" id="faq-start"><p class="info-note">Bạn có thể mở nhiều câu hỏi cùng lúc. Nếu chưa tìm thấy câu trả lời, <a href="/contact">liên hệ nhà sách</a> để được hỗ trợ.</p><div class="faq-list">
            <?php $questions = [
                ['Mộc Bản đang bán những loại sách nào?', 'Mộc Bản cung cấp sách văn học, kinh tế, kỹ năng sống, công nghệ thông tin, truyện tranh, thiếu nhi, ngoại ngữ và sách giáo khoa. Bạn có thể dùng danh mục hoặc ô tìm kiếm để tìm sách nhanh hơn.'],
                ['Làm thế nào để tìm một cuốn sách?', 'Bạn có thể tìm theo tên sách hoặc tác giả tại ô tìm kiếm. Trang sách còn hỗ trợ lọc theo danh mục, khoảng giá và sắp xếp theo giá hoặc sách mới nhất.'],
                ['Mộc Bản hỗ trợ những phương thức thanh toán nào?', 'Hiện website hỗ trợ thanh toán khi nhận hàng (COD). Nếu hệ thống hiển thị lựa chọn thanh toán online, giao dịch sẽ được xác nhận theo trạng thái từ cổng thanh toán.'],
                ['Tôi có thể theo dõi đơn hàng ở đâu?', 'Sau khi đăng nhập, mở mục Lịch sử đơn hàng để xem mã đơn, sản phẩm, tổng tiền và trạng thái xử lý.'],
                ['Tôi có thể hủy đơn hàng không?', 'Bạn có thể gửi yêu cầu hủy khi đơn chưa chuyển sang trạng thái giao hàng hoặc hoàn tất. Nếu không còn nút hủy, hãy liên hệ nhà sách để kiểm tra tình trạng đơn.'],
                ['Tôi phải làm gì nếu chưa nhận được email xác thực?', 'Kiểm tra Spam hoặc Quảng cáo trước. Nếu vẫn chưa thấy, vào trang Đăng nhập, nhập email tại mục gửi lại email xác thực và thử lại sau ít phút.'],
                ['Tôi quên mật khẩu thì làm thế nào?', 'Mở trang Quên mật khẩu, nhập email đã đăng ký và mở liên kết đặt lại mật khẩu được gửi về hộp thư. Liên kết có hiệu lực trong 60 phút và chỉ dùng một lần.'],
                ['Làm thế nào để liên hệ với Mộc Bản?', 'Bạn có thể mở trang Liên hệ để gửi yêu cầu hỗ trợ. Khi liên hệ về đơn hàng, hãy cung cấp mã đơn để nhà sách kiểm tra nhanh hơn.'],
            ]; foreach ($questions as $index => [$question, $answer]): $id = 'faq-panel-' . ($index + 1); ?>
            <div class="faq-item"><h2><button class="faq-trigger" type="button" aria-expanded="false" aria-controls="<?= $id ?>" data-accordion-trigger><?= htmlspecialchars($question, ENT_QUOTES, 'UTF-8') ?><span class="material-symbols-outlined" aria-hidden="true">expand_more</span></button></h2><div id="<?= $id ?>" class="faq-panel" data-accordion-panel inert><div class="faq-panel-inner"><p><?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?></p></div></div></div>
            <?php endforeach; ?>
        </div></div>
        <div class="info-actions"><a class="button button-primary" href="/books">Khám phá sách</a><a class="button" href="/contact">Liên hệ nhà sách</a></div>
    </div>
</section>
