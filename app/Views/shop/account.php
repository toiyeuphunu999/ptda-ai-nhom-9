<?php

use App\Core\Csrf;
use App\Helpers\ViewHelper;

?>

<div class="container page-head">

    <?php require dirname(__DIR__) . '/partials/breadcrumbs.php'; ?>

    <p class="eyebrow">Tài khoản</p>

    <h1>Tài khoản cá nhân</h1>

</div>

<section class="container section account-grid">

    <!-- Thông tin cá nhân -->
    <div class="info-card">

        <h2>Thông tin cá nhân</h2>

        <p>
            <strong>
                <?= ViewHelper::escape($user['name'] ?? '') ?>
            </strong>
        </p>

        <p>
            <?= ViewHelper::escape($user['email'] ?? '') ?>
        </p>

        <p>
            Vai trò:
            <?= ViewHelper::escape(
                ViewHelper::userRole(
                    (string)($user['role'] ?? 'customer')
                )
            ) ?>
        </p>

        <p>
            Mã giới thiệu:
            <strong>
                <?= ViewHelper::escape(
                    $user['referral_code'] ?? 'Chưa có'
                ) ?>
            </strong>
        </p>

        <?php if (!empty($profileError)): ?>

            <div class="notice">
                <?= ViewHelper::escape($profileError) ?>
            </div>

        <?php endif; ?>

        <form
            id="account-profile-form"
            class="account-profile-form"
            method="post"
            action="/account"
        >

            <?= Csrf::field() ?>

            <label for="account-name">
                Họ và tên
            </label>

            <input
                id="account-name"
                name="name"
                type="text"
                maxlength="120"
                value="<?= ViewHelper::escape(
                    (string)($formName ?? $user['name'] ?? '')
                ) ?>"
                required
            >

        </form>

        <form
            id="account-logout-form"
            class="account-logout-form"
            method="post"
            action="/logout"
        >
            <?= Csrf::field() ?>
        </form>

        <div class="account-profile-actions">

            <button
                class="button"
                type="submit"
                form="account-profile-form"
            >
                Lưu họ tên
            </button>

            <button
                class="button"
                type="submit"
                form="account-logout-form"
            >
                Đăng xuất
            </button>

        </div>

    </div>

    <!-- Điểm thưởng -->
    <div class="info-card">

        <h2>Điểm thưởng</h2>

        <p class="book-price">
            <?= (int)($pointsBalance ?? 0) ?> điểm
        </p>

        <p>
            Điểm được cộng khi hoàn tất đơn hàng,
            đánh giá sách và giới thiệu thành viên mới.
        </p>

        <a href="/orders">
            Xem lịch sử đơn hàng
        </a>

    </div>

</section>

<!-- Thông báo tài khoản -->
<section
    class="container section"
    id="notifications"
>

    <div class="section-heading">

        <div>

            <p class="eyebrow">Cập nhật đơn hàng</p>

            <h2>
                Thông báo của tôi

                <?php if (($unreadCount ?? 0) > 0): ?>

                    <span style="
                        display:inline-block;
                        padding:4px 10px;
                        border-radius:20px;
                        background:#fce8e8;
                        color:#c62828;
                        font-size:13px;
                    ">
                        <?= (int)$unreadCount ?> mới
                    </span>

                <?php endif; ?>

            </h2>

        </div>

        <?php if (($unreadCount ?? 0) > 0): ?>

            <form
                method="post"
                action="/account/notifications/read-all"
            >

                <?= Csrf::field() ?>

                <button
                    type="submit"
                    class="button"
                >
                    Đánh dấu tất cả đã đọc
                </button>

            </form>

        <?php endif; ?>

    </div>

    <?php if (empty($notifications)): ?>

        <div class="empty-state">

            <h3>Chưa có thông báo</h3>

            <p>
                Khi bạn đặt hàng hoặc đơn hàng thay đổi trạng thái,
                thông báo sẽ xuất hiện tại đây.
            </p>

        </div>

    <?php else: ?>

        <div class="notification-list">

            <?php foreach ($notifications as $notification): ?>

                <article
                    class="info-card"
                    style="
                        margin-bottom:14px;
                        border-left:4px solid <?= empty($notification['is_read'])
                            ? '#a66a3f'
                            : '#dddddd' ?>;
                    "
                >

                    <div>

                        <div style="
                            display:flex;
                            justify-content:space-between;
                            gap:12px;
                            flex-wrap:wrap;
                        ">

                            <h3>
                                <?= ViewHelper::escape(
                                    $notification['title']
                                ) ?>
                            </h3>

                            <?php if (empty($notification['is_read'])): ?>

                                <span style="
                                    color:#b42318;
                                    font-size:13px;
                                    font-weight:600;
                                ">
                                    Chưa đọc
                                </span>

                            <?php else: ?>

                                <span style="
                                    color:#16803c;
                                    font-size:13px;
                                ">
                                    Đã đọc
                                </span>

                            <?php endif; ?>

                        </div>

                        <p>
                            <?= ViewHelper::escape(
                                $notification['message']
                            ) ?>
                        </p>

                        <small>
                            <?= ViewHelper::escape(
                                $notification['created_at']
                            ) ?>
                        </small>

                        <?php if (!empty($notification['order_id'])): ?>

                            <p>
                                <a href="/orders">
                                    Xem đơn hàng
                                </a>
                            </p>

                        <?php endif; ?>

                        <?php if (empty($notification['is_read'])): ?>

                            <form
                                method="post"
                                action="/account/notifications/<?= (int)$notification['id'] ?>/read"
                            >

                                <?= Csrf::field() ?>

                                <button
                                    type="submit"
                                    class="button"
                                >
                                    Đánh dấu đã đọc
                                </button>

                            </form>

                        <?php endif; ?>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>

<!-- Lịch sử điểm thưởng -->
<section class="container section">

    <div class="section-heading">

        <div>

            <p class="eyebrow">Lịch sử</p>

            <h2>Biến động điểm thưởng</h2>

        </div>

    </div>

    <?php if (empty($pointsHistory)): ?>

        <div class="empty-state">

            <p>Bạn chưa có giao dịch điểm thưởng.</p>

        </div>

    <?php else: ?>

        <div class="table-wrap">

            <table class="admin-table">

                <thead>

                    <tr>
                        <th>Nội dung</th>
                        <th>Điểm</th>
                        <th>Thời gian</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($pointsHistory as $transaction): ?>

                        <tr>

                            <td>
                                <?= ViewHelper::escape(
                                    $transaction['description']
                                ) ?>
                            </td>

                            <td>
                                <?= (int)$transaction['points'] > 0
                                    ? '+'
                                    : '' ?>
                                <?= (int)$transaction['points'] ?>
                            </td>

                            <td>
                                <?= ViewHelper::escape(
                                    $transaction['created_at']
                                ) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>