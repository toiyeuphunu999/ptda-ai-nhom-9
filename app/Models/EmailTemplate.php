<?php

declare(strict_types=1);

namespace App\Core;

final class EmailTemplate
{
    public static function verification(string $name, string $link): string
    {
        return self::layout(
            'Xác thực email',
            'Hoàn tất đăng ký tài khoản',
            'Cảm ơn bạn đã đăng ký tại Nhà sách Mộc Bản. Hãy xác thực email để bắt đầu sử dụng tài khoản của bạn.',
            $link,
            'Xác thực email',
            'Liên kết này có hiệu lực trong 24 giờ.',
            $name
        );
    }

    public static function passwordReset(string $name, string $link): string
    {
        return self::layout(
            'Bảo mật tài khoản',
            'Đặt lại mật khẩu của bạn',
            'Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản Nhà sách Mộc Bản của bạn.',
            $link,
            'Đặt lại mật khẩu',
            'Liên kết này có hiệu lực trong 60 phút và chỉ sử dụng được một lần. Nếu bạn không yêu cầu thao tác này, hãy bỏ qua email.',
            $name
        );
    }

    private static function layout(string $eyebrow, string $title, string $intro, string $link, string $button, string $note, string $name): string
    {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
        $safeEyebrow = htmlspecialchars($eyebrow, ENT_QUOTES, 'UTF-8');
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeIntro = htmlspecialchars($intro, ENT_QUOTES, 'UTF-8');
        $safeButton = htmlspecialchars($button, ENT_QUOTES, 'UTF-8');
        $safeNote = htmlspecialchars($note, ENT_QUOTES, 'UTF-8');

        return '<!doctype html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>' . $safeTitle . ' | Nhà sách Mộc Bản</title></head><body style="margin:0;padding:0;background:#f8fafc;color:#111827;font-family:Arial,Helvetica,sans-serif;line-height:1.6;"><div style="padding:32px 16px;background:#f8fafc;"><div style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:4px;overflow:hidden;box-shadow:0 1px 2px rgba(15,23,42,.05);"><div style="padding:24px 32px;background:#1e3a5f;color:#ffffff;"><div style="font-size:21px;font-weight:700;letter-spacing:2px;">MỘC BẢN</div><div style="margin-top:4px;color:#dce9ff;font-size:11px;letter-spacing:2px;">NHÀ SÁCH TRỰC TUYẾN</div></div><div style="height:4px;background:#c2410c;"></div><div style="padding:34px 32px;"><div style="margin-bottom:8px;color:#9a3412;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;">' . $safeEyebrow . '</div><h1 style="margin:0 0 16px;color:#1e3a5f;font-size:28px;line-height:1.25;">' . $safeTitle . '</h1><p style="margin:0 0 12px;font-size:16px;">Xin chào ' . $safeName . ',</p><p style="margin:0 0 24px;color:#475569;font-size:15px;">' . $safeIntro . '</p><div style="text-align:center;margin:28px 0;"><a href="' . $safeLink . '" style="display:inline-block;padding:13px 22px;border-radius:4px;background:#c2410c;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">' . $safeButton . '</a></div><p style="margin:0;padding:14px 16px;border-left:3px solid #c2410c;background:#fff7ed;color:#7c2d12;font-size:13px;">' . $safeNote . '</p><p style="margin:24px 0 0;color:#64748b;font-size:13px;">Nếu nút trên không hoạt động, hãy sao chép liên kết sau vào trình duyệt:</p><p style="margin:6px 0 0;word-break:break-all;color:#1e3a5f;font-size:12px;">' . $safeLink . '</p></div><div style="padding:20px 32px;border-top:1px solid #e5e7eb;background:#f8fafc;color:#64748b;font-size:12px;"><strong style="color:#1e3a5f;">Nhà sách Mộc Bản</strong><br>Không gian chọn sách rõ ràng và dễ sử dụng.<br><span style="color:#94a3b8;">Email này được gửi tự động, vui lòng không trả lời.</span></div></div></div></body></html>';
    }
}
