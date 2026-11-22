<?php
use App\Core\Csrf;
use App\Helpers\ViewHelper;

$editing = !empty($book['id']);
$description = (string)($book['description'] ?? '');
?>

<div class="admin-panel form-layout">
    <?php if (!empty($error)): ?>
        <div class="notice"><?= ViewHelper::escape($error) ?></div>
    <?php endif; ?>

    <form class="form-card" method="post" data-book-form
        action="<?= $editing ? '/admin/books/' . (int)$book['id'] . '/update' : '/admin/books' ?>">
        <?= Csrf::field() ?>

        <label for="book-title">Tên sách</label>
        <input id="book-title" name="title" type="text" value="<?= ViewHelper::escape($book['title'] ?? '') ?>" required>

        <label for="author">Tác giả</label>
        <input id="author" name="author" type="text" value="<?= ViewHelper::escape($book['author'] ?? '') ?>" required>

        <label for="category">Danh mục</label>
        <select id="category" name="category_id" required>
            <option value="">Chọn danh mục</option>
            <?php foreach ($categories ?? [] as $category): ?>
                <option value="<?= (int)$category['id'] ?>" <?= ((int)($book['category_id'] ?? 0) === (int)$category['id']) ? 'selected' : '' ?>>
                    <?= ViewHelper::escape($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="price">Giá bán</label>
        <input id="price" name="price" type="number" min="0" value="<?= (int)($book['price'] ?? 0) ?>" required>

        <label for="original-price">Giá gốc</label>
        <input id="original-price" name="original_price" type="number" min="0" value="<?= $book['original_price'] ?? '' ?>">

        <label for="stock">Tồn kho</label>
        <input id="stock" name="stock_quantity" type="number" min="0" value="<?= (int)($book['stock_quantity'] ?? 0) ?>">

        <label for="description-editor">Mô tả ngắn</label>
        <textarea id="description" name="description" hidden><?= ViewHelper::escape($description) ?></textarea>
        <div class="book-description-editor" data-simple-editor>
            <div class="editor-toolbar" role="toolbar" aria-label="Định dạng mô tả">
                <button type="button" data-editor-command="bold"><strong>B</strong></button>
                <button type="button" data-editor-command="italic"><em>I</em></button>
                <button type="button" data-editor-command="underline"><u>U</u></button>
                <button type="button" data-editor-command="insertUnorderedList">• Danh sách</button>
                <button type="button" data-editor-command="insertOrderedList">1. Danh sách</button>
                <button type="button" data-editor-command="formatBlock" data-editor-value="blockquote">Trích dẫn</button>
                <button type="button" data-editor-command="removeFormat">Xóa định dạng</button>
            </div>
            <div id="description-editor" class="editor-content" contenteditable="true" data-editor-content="<?= ViewHelper::escape($description) ?>"></div>
        </div>
        <p class="editor-help">Có thể định dạng chữ, tạo danh sách và trích dẫn.</p>

        <label for="status">Trạng thái</label>
        <select id="status" name="status">
            <option value="draft">Nháp</option>
            <option value="published" <?= (($book['status'] ?? '') === 'published') ? 'selected' : '' ?>>Đang bán</option>
            <option value="hidden" <?= (($book['status'] ?? '') === 'hidden') ? 'selected' : '' ?>>Ẩn</option>
        </select>

        <button class="button button-primary" type="submit">
            <?= $editing ? 'Cập nhật sách' : 'Tạo sách' ?>
        </button>
    </form>
</div>

