-- Initial catalogue for bookstore_web212. Safe to re-run by unique slug checks.
INSERT INTO categories (name, slug, description) VALUES
('Văn học', 'van-hoc', 'Văn học Việt Nam và thế giới'),
('Kinh tế', 'kinh-te', 'Kinh doanh, quản trị và tài chính'),
('Kỹ năng sống', 'ky-nang-song', 'Thói quen, tư duy và phát triển cá nhân'),
('Công nghệ thông tin', 'cong-nghe-thong-tin', 'Lập trình và công nghệ'),
('Truyện tranh', 'truyen-tranh', 'Truyện tranh và manga'),
('Thiếu nhi', 'thieu-nhi', 'Sách khám phá dành cho thiếu nhi'),
('Ngoại ngữ', 'ngoai-ngu', 'Tài liệu học ngoại ngữ'),
('Sách giáo khoa', 'sach-giao-khoa', 'Sách giáo khoa phổ thông')
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

INSERT INTO books (category_id, title, slug, author, publisher, price, original_price, description, cover, stock_quantity, status)
    SELECT c.id, v.title, v.slug, v.author, 'Nhà sách Mộc Bản', v.price, v.original_price, v.description, NULL, v.stock, 'published'
FROM categories c JOIN (
    SELECT 'van-hoc' category_slug, 'Hà Nội bốn mùa' title, 'Nguyễn Nhật Ánh' author, 'ha-noi-bon-mua' slug, 89000 price, 105000 original_price, 'Một lát cắt dịu dàng về ký ức và những cuộc gặp gỡ ở Hà Nội.' description, 18 stock UNION ALL
    SELECT 'van-hoc','Mắt biếc','Nguyễn Nhật Ánh','mat-biec',99000,120000,'Câu chuyện tuổi thơ và tình yêu trong trẻo.',25 UNION ALL
    SELECT 'van-hoc','Nhà giả kim','Paulo Coelho','nha-gia-kim',79000,95000,'Hành trình đi tìm kho báu và lắng nghe tiếng gọi bên trong.',32 UNION ALL
    SELECT 'van-hoc','Người đua diều','Khaled Hosseini','nguoi-dua-dieu',125000,150000,'Câu chuyện về tình bạn, lỗi lầm và cơ hội chuộc lại quá khứ.',12 UNION ALL
    SELECT 'van-hoc','Điều kỳ diệu của tiệm tạp hóa Namiya','Higashino Keigo','dieu-ky-dieu-cua-tiem-tap-hoa-namiya',108000,130000,'Những lá thư vượt thời gian kết nối các số phận xa lạ.',9 UNION ALL
    SELECT 'kinh-te','Tư duy nhanh và chậm','Daniel Kahneman','tu-duy-nhanh-va-cham',159000,195000,'Khám phá hai hệ thống tư duy chi phối lựa chọn.',14 UNION ALL
    SELECT 'kinh-te','Chiến lược đại dương xanh','W. Chan Kim','chien-luoc-dai-duong-xanh',135000,165000,'Cách tạo ra không gian thị trường mới.',11 UNION ALL
    SELECT 'kinh-te','Từ tốt đến vĩ đại','Jim Collins','tu-tot-den-vi-dai',145000,175000,'Những nguyên tắc tạo chuyển biến bền vững.',20 UNION ALL
    SELECT 'kinh-te','Khởi nghiệp tinh gọn','Eric Ries','khoi-nghiep-tinh-gon',118000,140000,'Xây dựng sản phẩm qua các vòng lặp học hỏi ngắn.',7 UNION ALL
    SELECT 'kinh-te','Marketing trong kỷ nguyên 4.0','Philip Kotler','marketing-trong-ky-nguyen-4-0',149000,180000,'Nền tảng marketing hiện đại trong bối cảnh kết nối.',16 UNION ALL
    SELECT 'ky-nang-song','Atomic Habits','James Clear','atomic-habits',129000,159000,'Thay đổi nhỏ mỗi ngày để hình thành thói quen tốt.',28 UNION ALL
    SELECT 'ky-nang-song','Đừng lựa chọn an nhàn khi còn trẻ','Cảnh Thiên','dung-lua-chon-an-nhan',85000,105000,'Gợi ý thực tế để chủ động học hỏi.',21 UNION ALL
    SELECT 'ky-nang-song','7 thói quen hiệu quả','Stephen R. Covey','7-thoi-quen-hieu-qua',135000,165000,'Các nguyên tắc rèn luyện tính chủ động và hợp tác.',10 UNION ALL
    SELECT 'ky-nang-song','Bài học diệu kỳ từ chiếc xe rác','David J. Pollay','bai-hoc-dieu-ky-tu-chiec-xe-rac',75000,90000,'Cách giữ năng lượng trước những điều tiêu cực.',19 UNION ALL
    SELECT 'ky-nang-song','Làm chủ tuổi trẻ','Hanyu','lam-chu-tuoi-tre',99000,119000,'Một cuốn sách về mục tiêu, kỷ luật và bắt đầu.',13 UNION ALL
    SELECT 'cong-nghe-thong-tin','Clean Code','Robert C. Martin','clean-code',249000,295000,'Các nguyên tắc viết mã nguồn rõ ràng và dễ bảo trì.',8 UNION ALL
    SELECT 'cong-nghe-thong-tin','Giải thuật và lập trình','Lê Minh Hoàng','giai-thuat-va-lap-trinh',155000,185000,'Tư duy giải bài toán bằng cấu trúc dữ liệu.',17 UNION ALL
    SELECT 'cong-nghe-thong-tin','PHP và MySQL','Nguyễn Trường Sinh','php-va-mysql',169000,199000,'Thực hành xây dựng ứng dụng web với PHP và MySQL.',15 UNION ALL
    SELECT 'cong-nghe-thong-tin','Nhập môn trí tuệ nhân tạo','Nguyễn Thanh Thủy','nhap-mon-tri-tue-nhan-tao',189000,220000,'Các khái niệm nền tảng của AI và dữ liệu.',6 UNION ALL
    SELECT 'cong-nghe-thong-tin','JavaScript cơ bản','Phạm Hồng Thái','javascript-co-ban',142000,170000,'Làm quen JavaScript hiện đại qua giao diện thực tế.',22 UNION ALL
    SELECT 'truyen-tranh','Doraemon - Tập 1','Fujiko F. Fujio','doraemon-tap-1',22000,25000,'Những câu chuyện hài hước và ấm áp của Nobita.',40 UNION ALL
    SELECT 'truyen-tranh','Thám tử lừng danh Conan - Tập 1','Gosho Aoyama','conan-tap-1',25000,28000,'Vụ án đầu tiên của thám tử nhí Conan.',35 UNION ALL
    SELECT 'truyen-tranh','One Piece - Tập 1','Eiichiro Oda','one-piece-tap-1',24000,27000,'Luffy bắt đầu chuyến phiêu lưu trên biển.',30 UNION ALL
    SELECT 'truyen-tranh','Nhật ký chú bé nhút nhát','Jeff Kinney','nhat-ky-chu-be-nhut-nhat',65000,78000,'Những tình huống dở khóc dở cười ở trường học.',18 UNION ALL
    SELECT 'truyen-tranh','Winnie-the-Pooh','A. A. Milne','winnie-the-pooh',72000,85000,'Thế giới nhỏ đầy tình bạn trong khu rừng.',10 UNION ALL
    SELECT 'thieu-nhi','Để con được lớn','Lê Nguyễn Nhật Linh','de-con-duoc-lon',68000,80000,'Câu chuyện giúp trẻ hiểu cảm xúc và tự tin.',24 UNION ALL
    SELECT 'thieu-nhi','Hạt giống tâm hồn thiếu nhi','Nhiều tác giả','hat-giong-tam-hon',55000,65000,'Những câu chuyện về lòng tốt và sẻ chia.',16 UNION ALL
    SELECT 'thieu-nhi','Từ điển bằng hình đầu tiên','DK Publishing','tu-dien-bang-hinh-dau-tien',175000,210000,'Mở rộng vốn từ qua hình ảnh gần gũi.',5 UNION ALL
    SELECT 'thieu-nhi','Những câu chuyện vàng','Nguyễn Đông Hải','nhung-cau-chuyen-vang',62000,75000,'Truyện ngắn nuôi dưỡng trí tưởng tượng.',27 UNION ALL
    SELECT 'thieu-nhi','Khám phá vũ trụ','Carole Stott','kham-pha-vu-tru',145000,170000,'Hành trình tìm hiểu các hành tinh.',8 UNION ALL
    SELECT 'ngoai-ngu','English Grammar in Use','Raymond Murphy','english-grammar-in-use',215000,250000,'Ngữ pháp tiếng Anh thực hành trình độ trung cấp.',14 UNION ALL
    SELECT 'ngoai-ngu','English Vocabulary in Use - Elementary','Michael McCarthy','vocabulary-in-use-elementary',198000,230000,'Học từ vựng theo chủ đề và bài tập.',11 UNION ALL
    SELECT 'ngoai-ngu','Tự học tiếng Nhật cho người mới bắt đầu','Mai Lan','tu-hoc-tieng-nhat',99000,120000,'Làm quen bảng chữ cái và giao tiếp cơ bản.',20 UNION ALL
    SELECT 'ngoai-ngu','Giáo trình tiếng Hàn tổng hợp 1','Đại học Seoul','han-ngu-tong-hop',135000,160000,'Giáo trình nhập môn tiếng Hàn.',9 UNION ALL
    SELECT 'ngoai-ngu','IELTS Reading Practice','Collins','ielts-reading-practice',185000,220000,'Bài đọc luyện thi IELTS theo dạng câu hỏi.',7 UNION ALL
    SELECT 'sach-giao-khoa','Toán 10 - Kết nối tri thức','Bộ Giáo dục và Đào tạo','toan-10-ket-noi-tri-thuc',28000,32000,'Sách giáo khoa Toán lớp 10.',45 UNION ALL
    SELECT 'sach-giao-khoa','Ngữ văn 10 - Cánh diều','Bộ Giáo dục và Đào tạo','ngu-van-10-canh-dieu',31000,35000,'Sách giáo khoa Ngữ văn lớp 10.',36 UNION ALL
    SELECT 'sach-giao-khoa','Vật lý 12','Bộ Giáo dục và Đào tạo','vat-ly-12',27000,30000,'Sách giáo khoa Vật lý lớp 12.',29 UNION ALL
    SELECT 'sach-giao-khoa','Hóa học 11','Bộ Giáo dục và Đào tạo','hoa-hoc-11',26000,30000,'Sách giáo khoa Hóa học lớp 11.',31 UNION ALL
    SELECT 'sach-giao-khoa','Lịch sử 9','Bộ Giáo dục và Đào tạo','lich-su-9',24000,28000,'Sách giáo khoa Lịch sử lớp 9.',33
) v ON c.slug = v.category_slug
ON DUPLICATE KEY UPDATE title = VALUES(title), price = VALUES(price), original_price = VALUES(original_price), stock_quantity = VALUES(stock_quantity), status = VALUES(status);

-- Nhà xuất bản theo các ấn bản phổ biến tại Việt Nam.
UPDATE books SET publisher = CASE title
    WHEN 'Hà Nội bốn mùa' THEN 'Nhà xuất bản Trẻ'
    WHEN 'Mắt biếc' THEN 'Nhà xuất bản Trẻ'
    WHEN 'Nhà giả kim' THEN 'Nhã Nam'
    WHEN 'Người đua diều' THEN 'Nhã Nam'
    WHEN 'Điều kỳ diệu của tiệm tạp hóa Namiya' THEN 'Nhã Nam'
    WHEN 'Tư duy nhanh và chậm' THEN 'Nhà xuất bản Thế Giới'
    WHEN 'Chiến lược đại dương xanh' THEN 'Nhà xuất bản Lao Động'
    WHEN 'Từ tốt đến vĩ đại' THEN 'Nhà xuất bản Trẻ'
    WHEN 'Khởi nghiệp tinh gọn' THEN 'Nhà xuất bản Tổng hợp Thành phố Hồ Chí Minh'
    WHEN 'Marketing trong kỷ nguyên 4.0' THEN 'Nhà xuất bản Trẻ'
    WHEN 'Atomic Habits' THEN 'Nhà xuất bản Thế Giới'
    WHEN 'Đừng lựa chọn an nhàn khi còn trẻ' THEN 'Nhà xuất bản Văn Học'
    WHEN '7 thói quen hiệu quả' THEN 'Nhà xuất bản Tổng hợp Thành phố Hồ Chí Minh'
    WHEN 'Bài học diệu kỳ từ chiếc xe rác' THEN 'Nhà xuất bản Trẻ'
    WHEN 'Làm chủ tuổi trẻ' THEN 'Nhà xuất bản Văn Học'
    WHEN 'Clean Code' THEN 'Nhà xuất bản Lao Động'
    WHEN 'Giải thuật và lập trình' THEN 'Nhà xuất bản Giáo dục Việt Nam'
    WHEN 'PHP và MySQL' THEN 'Nhà xuất bản Thông tin và Truyền thông'
    WHEN 'Nhập môn trí tuệ nhân tạo' THEN 'Nhà xuất bản Đại học Quốc gia Hà Nội'
    WHEN 'JavaScript cơ bản' THEN 'Nhà xuất bản Thông tin và Truyền thông'
    WHEN 'Doraemon - Tập 1' THEN 'Nhà xuất bản Kim Đồng'
    WHEN 'Thám tử lừng danh Conan - Tập 1' THEN 'Nhà xuất bản Kim Đồng'
    WHEN 'One Piece - Tập 1' THEN 'Nhà xuất bản Kim Đồng'
    WHEN 'Nhật ký chú bé nhút nhát' THEN 'Nhà xuất bản Kim Đồng'
    WHEN 'Winnie-the-Pooh' THEN 'Nhà xuất bản Kim Đồng'
    WHEN 'Để con được lớn' THEN 'Nhà xuất bản Phụ Nữ Việt Nam'
    WHEN 'Hạt giống tâm hồn thiếu nhi' THEN 'Nhà xuất bản Tổng hợp Thành phố Hồ Chí Minh'
    WHEN 'Từ điển bằng hình đầu tiên' THEN 'Nhà xuất bản Thế Giới'
    WHEN 'Những câu chuyện vàng' THEN 'Nhà xuất bản Kim Đồng'
    WHEN 'Khám phá vũ trụ' THEN 'Nhà xuất bản Kim Đồng'
    WHEN 'English Grammar in Use' THEN 'Cambridge University Press'
    WHEN 'English Vocabulary in Use - Elementary' THEN 'Cambridge University Press'
    WHEN 'Tự học tiếng Nhật cho người mới bắt đầu' THEN 'Nhà xuất bản Hồng Đức'
    WHEN 'Giáo trình tiếng Hàn tổng hợp 1' THEN 'Nhà xuất bản Đại học Quốc gia Hà Nội'
    WHEN 'IELTS Reading Practice' THEN 'Collins'
    WHEN 'Toán 10 - Kết nối tri thức' THEN 'Nhà xuất bản Giáo dục Việt Nam'
    WHEN 'Ngữ văn 10 - Cánh diều' THEN 'Nhà xuất bản Đại học Sư phạm'
    WHEN 'Vật lý 12' THEN 'Nhà xuất bản Giáo dục Việt Nam'
    WHEN 'Hóa học 11' THEN 'Nhà xuất bản Giáo dục Việt Nam'
    WHEN 'Lịch sử 9' THEN 'Nhà xuất bản Giáo dục Việt Nam'
    ELSE publisher
END
WHERE publisher = 'Dữ liệu minh họa';
