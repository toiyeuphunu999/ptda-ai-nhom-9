<?php

use App\Core\Csrf;
use App\Helpers\ViewHelper;

$editing = !empty($book['id']);

?>

<div class="admin-panel form-layout">

    <?php if (!empty($error)): ?>

        <div class="notice">
            <?= ViewHelper::escape($error) ?>
        </div>

    <?php endif; ?>

    <form
        class="form-card"
        method="post"
        action="<?= $editing
            ? '/admin/books/' . (int)$book['id'] . '/update'
            : '/admin/books' ?>"
    >

        <?= Csrf::field() ?>

        <!-- Tên sách -->
        <label for="book-title">
            Tên sách
        </label>

        <input
            id="book-title"
            name="title"
            type="text"
            value="<?= ViewHelper::escape($book['title'] ?? '') ?>"
            required
        >

        <!-- Tác giả -->
        <label for="author">
            Tác giả
        </label>

        <input
            id="author"
            name="author"
            type="text"
            value="<?= ViewHelper::escape($book['author'] ?? '') ?>"
            required
        >

        <!-- Nhà xuất bản -->
        <label for="publisher">
            Nhà xuất bản
        </label>

        <input
            id="publisher"
            name="publisher"
            type="text"
            maxlength="180"
            value="<?= ViewHelper::escape($book['publisher'] ?? '') ?>"
            placeholder="Ví dụ: Nhà xuất bản Kim Đồng"
        >

        <!-- Năm xuất bản -->
        <label for="published-year">
            Năm xuất bản
        </label>

        <input
            id="published-year"
            name="published_year"
            type="number"
            min="1000"
            max="<?= (int)date('Y') + 1 ?>"
            value="<?= ViewHelper::escape(
                (string)($book['published_year'] ?? '')
            ) ?>"
            placeholder="Ví dụ: 2024"
        >

        <!-- Danh mục -->
        <label for="category">
            Danh mục
        </label>

        <select
            id="category"
            name="category_id"
            required
        >

            <option value="">
                Chọn danh mục
            </option>

            <?php foreach ($categories ?? [] as $category): ?>

                <option
                    value="<?= (int)$category['id'] ?>"
                    <?= (
                        (int)($book['category_id'] ?? 0)
                        === (int)$category['id']
                    ) ? 'selected' : '' ?>
                >
                    <?= ViewHelper::escape($category['name']) ?>
                </option>

            <?php endforeach; ?>

        </select>

        <!-- Giá bán -->
        <label for="price">
            Giá bán
        </label>

        <input
            id="price"
            name="price"
            type="number"
            min="0"
            value="<?= (int)($book['price'] ?? 0) ?>"
            required
        >

        <!-- Giá gốc -->
        <label for="original-price">
            Giá gốc
        </label>

        <input
            id="original-price"
            name="original_price"
            type="number"
            min="0"
            value="<?= ViewHelper::escape(
                (string)($book['original_price'] ?? '')
            ) ?>"
        >

        <!-- Tồn kho -->
        <label for="stock">
            Số lượng tồn kho
        </label>

        <input
            id="stock"
            name="stock_quantity"
            type="number"
            min="0"
            value="<?= (int)($book['stock_quantity'] ?? 0) ?>"
        >

        <!-- Mô tả -->
        <label for="description">
            Mô tả sách
        </label>

        <textarea
            id="description"
            name="description"
            rows="5"
        ><?= ViewHelper::escape($book['description'] ?? '') ?></textarea>

        <!-- Trạng thái -->
        <label for="status">
            Trạng thái
        </label>

        <select id="status" name="status">

            <option
                value="draft"
                <?= (($book['status'] ?? '') === 'draft')
                    ? 'selected'
                    : '' ?>
            >
                Nháp
            </option>

            <option
                value="published"
                <?= (($book['status'] ?? '') === 'published')
                    ? 'selected'
                    : '' ?>
            >
                Đang bán
            </option>

            <option
                value="hidden"
                <?= (($book['status'] ?? '') === 'hidden')
                    ? 'selected'
                    : '' ?>
            >
                Ẩn
            </option>

        </select>

        <button
            class="button button-primary"
            type="submit"
        >
            <?= $editing ? 'Cập nhật sách' : 'Tạo sách' ?>
        </button>

    </form>

</div>