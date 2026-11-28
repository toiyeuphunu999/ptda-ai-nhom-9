<?php
use App\Core\Csrf;
use App\Helpers\ViewHelper;

$editing = !empty($book['id']);
$description = ViewHelper::richText((string)($book['description'] ?? ''));
$previewContent = ViewHelper::richText((string)($book['preview_content'] ?? ''));
?>

<div class="admin-panel form-layout border border-slate-200 rounded bg-white p-6 shadow-sm max-w-none min-w-0 mb-1 max-w-[780px]">
    <?php if (!empty($error)): ?>
        <div class="notice px-3.5 py-3 mb-4 border-l-[3px] border-orange-500 bg-orange-50 text-orange-900"><?= ViewHelper::escape($error) ?></div>
    <?php endif; ?>

    <form class="form-card border border-slate-200 rounded bg-white p-6 shadow-sm grid gap-2 [&_label]:mt-1.5 [&_label]:text-slate-900 [&_label]:text-[13px] [&_label]:font-bold [&_select]:min-h-10 [&_select]:leading-[1.4] [&_select]:cursor-pointer [&_select_option]:px-3 py-2 [&_select_option]:bg-white [&_select_option]:text-slate-900 [&_select:hover]:[border-color:#94a3b8] [&_select:focus-visible]:[border-color:#1e3a5f] [&_select:focus-visible]:[outline:2px_solid_#dbeafe] [&_select:focus-visible]:outline-offset-1 [&_select]:appearance-none [&_select]:[background-repeat:no-repeat] [&_select]:[background-position:right_12px_center] [&_select]:[background-size:16px_16px] [&_select]:pr-10" method="post" data-book-form
        action="<?= $editing ? '/admin/books/' . (int)$book['id'] . '/update' : '/admin/books' ?>">
        <?= Csrf::field() ?>

        <label for="book-title">Tên sách</label>
        <input class="block w-full min-h-10 rounded-md border border-[#cbd5e1] bg-white px-3 py-2 text-sm text-[#172033] outline-none transition placeholder:text-[#94a3b8] hover:border-[#94a3b8] focus:border-[#1e3a5f] focus:ring-4 focus:ring-[#dbeafe] disabled:cursor-not-allowed disabled:bg-[#f1f5f9]" id="book-title" name="title" type="text" value="<?= ViewHelper::escape($book['title'] ?? '') ?>" required>

        <label for="author">Tác giả</label>
        <input class="block w-full min-h-10 rounded-md border border-[#cbd5e1] bg-white px-3 py-2 text-sm text-[#172033] outline-none transition placeholder:text-[#94a3b8] hover:border-[#94a3b8] focus:border-[#1e3a5f] focus:ring-4 focus:ring-[#dbeafe] disabled:cursor-not-allowed disabled:bg-[#f1f5f9]" id="author" name="author" type="text" value="<?= ViewHelper::escape($book['author'] ?? '') ?>" required>

        <label for="category">Danh mục</label>
        <select class="block min-h-10 w-full cursor-pointer rounded-md border border-[#cbd5e1] bg-white px-3 py-2 text-sm text-[#172033] outline-none transition hover:border-[#94a3b8] focus:border-[#1e3a5f] focus:ring-4 focus:ring-[#dbeafe] disabled:cursor-not-allowed disabled:bg-[#f1f5f9]" id="category" name="category_id" required>
            <option value="">Chọn danh mục</option>
            <?php foreach ($categories ?? [] as $category): ?>
                <option value="<?= (int)$category['id'] ?>" <?= ((int)($book['category_id'] ?? 0) === (int)$category['id']) ? 'selected' : '' ?>>
                    <?= ViewHelper::escape($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="price">Giá bán</label>
        <input class="block w-full min-h-10 rounded-md border border-[#cbd5e1] bg-white px-3 py-2 text-sm text-[#172033] outline-none transition placeholder:text-[#94a3b8] hover:border-[#94a3b8] focus:border-[#1e3a5f] focus:ring-4 focus:ring-[#dbeafe] disabled:cursor-not-allowed disabled:bg-[#f1f5f9]" id="price" name="price" type="number" min="0" value="<?= (int)($book['price'] ?? 0) ?>" required>

        <label for="original-price">Giá gốc</label>
        <input class="block w-full min-h-10 rounded-md border border-[#cbd5e1] bg-white px-3 py-2 text-sm text-[#172033] outline-none transition placeholder:text-[#94a3b8] hover:border-[#94a3b8] focus:border-[#1e3a5f] focus:ring-4 focus:ring-[#dbeafe] disabled:cursor-not-allowed disabled:bg-[#f1f5f9]" id="original-price" name="original_price" type="number" min="0" value="<?= $book['original_price'] ?? '' ?>">

        <label for="stock">Tồn kho</label>
        <input class="block w-full min-h-10 rounded-md border border-[#cbd5e1] bg-white px-3 py-2 text-sm text-[#172033] outline-none transition placeholder:text-[#94a3b8] hover:border-[#94a3b8] focus:border-[#1e3a5f] focus:ring-4 focus:ring-[#dbeafe] disabled:cursor-not-allowed disabled:bg-[#f1f5f9]" id="stock" name="stock_quantity" type="number" min="0" value="<?= (int)($book['stock_quantity'] ?? 0) ?>">

        <label for="description">Mô tả ngắn</label>
        <textarea class="min-h-48 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-100" id="description" name="description" data-jodit-editor data-jodit-placeholder="Mô tả ngắn của sách..."><?= ViewHelper::escape($description) ?></textarea>




        <label for="preview_content">Nội dung đọc thử</label>
        <textarea class="min-h-48 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-100" id="preview_content" name="preview_content" data-jodit-editor data-jodit-placeholder="Nội dung đọc thử cho khách hàng..."><?= ViewHelper::escape($previewContent) ?></textarea>


        <label for="status">Trạng thái</label>
        <select class="block min-h-10 w-full cursor-pointer rounded-md border border-[#cbd5e1] bg-white px-3 py-2 text-sm text-[#172033] outline-none transition hover:border-[#94a3b8] focus:border-[#1e3a5f] focus:ring-4 focus:ring-[#dbeafe] disabled:cursor-not-allowed disabled:bg-[#f1f5f9]" id="status" name="status">
            <option value="draft">Nháp</option>
            <option value="published" <?= (($book['status'] ?? '') === 'published') ? 'selected' : '' ?>>Đang bán</option>
            <option value="hidden" <?= (($book['status'] ?? '') === 'hidden') ? 'selected' : '' ?>>Ẩn</option>
        </select>

        <button class="button button-primary !bg-[#c2410c] !text-white !border-[#c2410c] hover:!bg-[#9a3412] hover:!border-[#9a3412] inline-flex items-center justify-center gap-1.5 min-h-10 px-4 py-2.5 border border-slate-200 rounded bg-white text-blue-950 font-semibold whitespace-nowrap hover:bg-blue-50 hover:no-underline [&:focus-visible]:[outline:2px_solid_#93c5fd] [&:focus-visible]:outline-offset-2 [border-color:#f97316] bg-orange-500 text-white hover:[border-color:#ea580c] hover:bg-orange-600 hover:text-white bg-orange-700 [border-color:#c2410c] hover:bg-orange-800 hover:[border-color:#9a3412] hover:[border-color:#7c2d12]" type="submit">
            <?= $editing ? 'Cập nhật sách' : 'Tạo sách' ?>
        </button>
    </form>
</div>
