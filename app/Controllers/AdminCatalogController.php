<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Models\Book;
use App\Models\Category;
use RuntimeException;
use Throwable;

final class AdminCatalogController extends AdminController
{
    // Danh sách sách
    public function books(): void
    {
        $this->authorize();

        $this->view->render(
            'admin/books',
            [
                'title' => 'Quản lý sách',
                'books' => (new Book($this->database()))
                    ->allForAdmin()
            ],
            'admin'
        );
    }

    // Form thêm sách
    public function bookCreate(): void
    {
        $this->authorize();

        $this->view->render(
            'admin/book_form',
            [
                'title' => 'Thêm sách',
                'categories' => (new Category($this->database()))
                    ->all()
            ],
            'admin'
        );
    }

    // Form sửa sách
    public function bookEdit(string $id): void
    {
        $this->authorize();

        $database = $this->database();

        $this->view->render(
            'admin/book_form',
            [
                'title' => 'Chỉnh sửa sách',
                'book' => (new Book($database))
                    ->findById((int)$id),
                'categories' => (new Category($database))
                    ->all()
            ],
            'admin'
        );
    }

    // Thêm sách
    public function bookStore(): void
    {
        $this->authorize();

        try {

            Csrf::verify($_POST['_csrf'] ?? null);

            (new Book($this->database()))
                ->create($this->bookData());

            Session::flash(
                'success',
                'Đã thêm sách thành công.'
            );

            header('Location: /admin/books');

        } catch (Throwable $exception) {

            http_response_code(422);

            $this->view->render(
                'admin/book_form',
                [
                    'title' => 'Thêm sách',
                    'error' => $exception->getMessage(),
                    'categories' => (new Category($this->database()))
                        ->all()
                ],
                'admin'
            );
        }
    }

    // Cập nhật sách
    public function bookUpdate(string $id): void
    {
        $this->authorize();

        try {

            Csrf::verify($_POST['_csrf'] ?? null);

            (new Book($this->database()))
                ->update((int)$id, $this->bookData());

            Session::flash(
                'success',
                'Đã cập nhật sách thành công.'
            );

            header('Location: /admin/books');

        } catch (Throwable $exception) {

            http_response_code(422);

            $database = $this->database();

            $this->view->render(
                'admin/book_form',
                [
                    'title' => 'Chỉnh sửa sách',
                    'error' => $exception->getMessage(),
                    'book' => (new Book($database))
                        ->findById((int)$id),
                    'categories' => (new Category($database))
                        ->all()
                ],
                'admin'
            );
        }
    }

    // Xóa sách
    public function bookDelete(string $id): void
    {
        $this->authorize();

        Csrf::verify($_POST['_csrf'] ?? null);

        (new Book($this->database()))
            ->delete((int)$id);

        Session::flash(
            'success',
            'Đã xóa sách thành công.'
        );

        header('Location: /admin/books');
    }

    // Danh mục
    public function categories(): void
    {
        $this->authorize();

        $this->view->render(
            'admin/categories',
            [
                'title' => 'Danh mục',
                'categories' => (new Category($this->database()))
                    ->all()
            ],
            'admin'
        );
    }

    // Thêm danh mục
    public function categoryStore(): void
    {
        $this->authorize();

        try {

            Csrf::verify($_POST['_csrf'] ?? null);

            [$name, $slug, $description] = $this->categoryData();

            (new Category($this->database()))
                ->create($name, $slug, $description);

            Session::flash(
                'success',
                'Đã thêm danh mục thành công.'
            );

            header('Location: /admin/categories');

        } catch (Throwable $exception) {

            http_response_code(422);

            $this->view->render(
                'admin/categories',
                [
                    'title' => 'Danh mục',
                    'error' => $exception->getMessage(),
                    'categories' => (new Category($this->database()))
                        ->all()
                ],
                'admin'
            );
        }
    }

    // Cập nhật danh mục
    public function categoryUpdate(string $id): void
    {
        $this->authorize();

        try {

            Csrf::verify($_POST['_csrf'] ?? null);

            [$name, $slug, $description] = $this->categoryData();

            (new Category($this->database()))
                ->update((int)$id, $name, $slug, $description);

            Session::flash(
                'success',
                'Đã cập nhật danh mục thành công.'
            );

            header('Location: /admin/categories');

        } catch (Throwable $exception) {

            http_response_code(422);

            $this->view->render(
                'admin/categories',
                [
                    'title' => 'Danh mục',
                    'error' => $exception->getMessage(),
                    'categories' => (new Category($this->database()))
                        ->all()
                ],
                'admin'
            );
        }
    }

    // Xóa danh mục
    public function categoryDelete(string $id): void
    {
        $this->authorize();

        Csrf::verify($_POST['_csrf'] ?? null);

        (new Category($this->database()))
            ->delete((int)$id);

        Session::flash(
            'success',
            'Đã xóa danh mục thành công.'
        );

        header('Location: /admin/categories');
    }

    // Lấy dữ liệu sách từ form
    private function bookData(): array
    {
        $title = trim(
            (string)($_POST['title'] ?? '')
        );

        $author = trim(
            (string)($_POST['author'] ?? '')
        );

        $publisher = trim(
            (string)($_POST['publisher'] ?? '')
        );

        $price = (int)($_POST['price'] ?? 0);

        $original = trim(
            (string)($_POST['original_price'] ?? '')
        );

        $categoryId = (int)($_POST['category_id'] ?? 0);

        $yearInput = trim(
            (string)($_POST['published_year'] ?? '')
        );

        $year = null;

        if ($yearInput !== '') {

            $year = filter_var(
                $yearInput,
                FILTER_VALIDATE_INT,
                [
                    'options' => [
                        'min_range' => 1000,
                        'max_range' => (int)date('Y') + 1
                    ]
                ]
            );

            if ($year === false) {
                throw new RuntimeException(
                    'Năm xuất bản không hợp lệ.'
                );
            }
        }

        if (
            $title === '' ||
            $author === '' ||
            $categoryId < 1 ||
            $price < 0 ||
            (
                $original !== '' &&
                (int)$original < $price
            )
        ) {
            throw new RuntimeException(
                'Vui lòng kiểm tra tên sách, tác giả, danh mục và giá sách.'
            );
        }

        return [
            'category_id' => $categoryId,
            'title' => $title,
            'author' => $author,
            'publisher' => $publisher !== ''
                ? $publisher
                : null,
            'published_year' => $year,
            'price' => $price,
            'original_price' => $original === ''
                ? null
                : (int)$original,
            'description' => trim(
                (string)($_POST['description'] ?? '')
            ) ?: null,
            'stock_quantity' => max(
                0,
                (int)($_POST['stock_quantity'] ?? 0)
            ),
            'status' => in_array(
                $_POST['status'] ?? 'draft',
                ['draft', 'published', 'hidden'],
                true
            ) ? $_POST['status'] : 'draft'
        ];
    }

    // Dữ liệu danh mục
    private function categoryData(): array
    {
        $name = trim(
            (string)($_POST['name'] ?? '')
        );

        if ($name === '') {
            throw new RuntimeException(
                'Tên danh mục không được để trống.'
            );
        }

        return [
            $name,
            '',
            trim((string)($_POST['description'] ?? '')) ?: null
        ];
    }
}