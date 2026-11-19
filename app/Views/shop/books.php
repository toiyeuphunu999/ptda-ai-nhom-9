<?php

use App\Helpers\ViewHelper;

$filters = $filters ?? [
    'category' => '',
    'author' => '',
    'publisher' => '',
    'published_year' => null,
    'min_price' => null,
    'max_price' => null,
    'sort' => 'newest'
];

?>

<div class="container page-head">

    <?php require dirname(__DIR__) . '/partials/breadcrumbs.php'; ?>

    <p class="eyebrow">Bộ sưu tập</p>

    <h1>Tất cả sách</h1>

    <p class="lead">
        Tìm kiếm sách theo danh mục, tác giả,
        nhà xuất bản và năm xuất bản.
    </p>

</div>

<section class="container section">

    <form
        class="catalog-filters"
        method="get"
        action="/books"
    >

        <!-- Danh mục -->
        <div>
            <label for="filter-category">
                Danh mục
            </label>

            <select id="filter-category" name="category">

                <option value="">
                    Tất cả danh mục
                </option>

                <?php foreach ($categories ?? [] as $category): ?>

                    <option
                        value="<?= ViewHelper::escape($category['slug']) ?>"
                        <?= ($filters['category'] ?? '') === $category['slug']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= ViewHelper::escape($category['name']) ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <!-- Tác giả -->
        <div>
            <label for="filter-author">
                Tác giả
            </label>

            <select id="filter-author" name="author">

                <option value="">
                    Tất cả tác giả
                </option>

                <?php foreach ($authors ?? [] as $author): ?>

                    <option
                        value="<?= ViewHelper::escape($author) ?>"
                        <?= ($filters['author'] ?? '') === $author
                            ? 'selected'
                            : '' ?>
                    >
                        <?= ViewHelper::escape($author) ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <!-- Nhà xuất bản -->
        <div>
            <label for="filter-publisher">
                Nhà xuất bản
            </label>

            <select id="filter-publisher" name="publisher">

                <option value="">
                    Tất cả nhà xuất bản
                </option>

                <?php foreach ($publishers ?? [] as $publisher): ?>

                    <option
                        value="<?= ViewHelper::escape($publisher) ?>"
                        <?= ($filters['publisher'] ?? '') === $publisher
                            ? 'selected'
                            : '' ?>
                    >
                        <?= ViewHelper::escape($publisher) ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <!-- Năm xuất bản -->
        <div>
            <label for="filter-year">
                Năm xuất bản
            </label>

            <select id="filter-year" name="published_year">

                <option value="">
                    Tất cả năm
                </option>

                <?php foreach ($years ?? [] as $year): ?>

                    <option
                        value="<?= (int)$year ?>"
                        <?= (string)($filters['published_year'] ?? '') === (string)$year
                            ? 'selected'
                            : '' ?>
                    >
                        <?= (int)$year ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <!-- Giá từ -->
        <div>
            <label for="filter-min-price">
                Giá từ
            </label>

            <input
                id="filter-min-price"
                name="min_price"
                type="number"
                min="0"
                step="1000"
                value="<?= $filters['min_price'] !== null
                    ? (int)$filters['min_price']
                    : '' ?>"
                placeholder="VNĐ"
            >
        </div>

        <!-- Giá đến -->
        <div>
            <label for="filter-max-price">
                Giá đến
            </label>

            <input
                id="filter-max-price"
                name="max_price"
                type="number"
                min="0"
                step="1000"
                value="<?= $filters['max_price'] !== null
                    ? (int)$filters['max_price']
                    : '' ?>"
                placeholder="VNĐ"
            >
        </div>

        <!-- Sắp xếp -->
        <div>
            <label for="filter-sort">
                Sắp xếp
            </label>

            <select id="filter-sort" name="sort">

                <option
                    value="newest"
                    <?= ($filters['sort'] ?? '') === 'newest'
                        ? 'selected'
                        : '' ?>
                >
                    Mới nhất
                </option>

                <option
                    value="price_asc"
                    <?= ($filters['sort'] ?? '') === 'price_asc'
                        ? 'selected'
                        : '' ?>
                >
                    Giá thấp đến cao
                </option>

                <option
                    value="price_desc"
                    <?= ($filters['sort'] ?? '') === 'price_desc'
                        ? 'selected'
                        : '' ?>
                >
                    Giá cao đến thấp
                </option>

            </select>
        </div>

        <!-- Nút lọc -->
        <div class="filter-actions">

            <button
                class="button button-primary"
                type="submit"
            >
                Lọc sách
            </button>

            <a
                class="button"
                href="/books"
            >
                Xóa lọc
            </a>

        </div>

    </form>

    <p class="result-note">
        Có <?= count($books) ?> sách phù hợp.
    </p>

    <?php if (!empty($books)): ?>

        <div class="book-grid">

            <?php foreach ($books as $book): ?>

                <?php require dirname(__DIR__) . '/partials/book_card.php'; ?>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty-state">

            <h2>Không tìm thấy sách phù hợp</h2>

            <p>
                Hãy thử thay đổi nhà xuất bản,
                năm xuất bản hoặc điều kiện lọc.
            </p>

            <a
                class="button button-primary"
                href="/books"
            >
                Xem toàn bộ sách
            </a>

        </div>

    <?php endif; ?>

</section>