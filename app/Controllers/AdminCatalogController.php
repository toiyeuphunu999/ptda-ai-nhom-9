<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Models\Book;
use App\Models\Category;
use Throwable;

class AdminCatalogController extends AdminController
{
    public function books(): void
    {
        $this->authorize();
        $data = array('title' => 'Quản lý sách');
        $data['books'] = (new Book($this->database()))->allForAdmin();
        $this->view->render('admin/books', $data, 'admin');
    }

    public function bookCreate(): void
    {
        $this->authorize();
        $data = array('title' => 'Thêm sách');
        $data['categories'] = (new Category($this->database()))->all();
        $this->view->render('admin/book_form', $data, 'admin');
    }

    public function bookEdit(string $id): void
    {
        $this->authorize();
        $bookModel = new Book($this->database());
        $data = array('title' => 'Chỉnh sửa sách');
        $data['book'] = $bookModel->findById((int)$id);
        $data['categories'] = (new Category($this->database()))->all();
        $this->view->render('admin/book_form', $data, 'admin');
    }

    public function bookStore(): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $bookModel = new Book($this->database());
            $bookModel->create($bookModel->dataFromInput($_POST));
            Session::flash('success', 'Đã thêm sách thành công.');
            header('Location: /admin/books');
        } catch (Throwable $exception) {
            $data = array('title' => 'Thêm sách');
            $data['error'] = $exception->getMessage();
            $data['categories'] = (new Category($this->database()))->all();
            $this->view->render('admin/book_form', $data, 'admin');
        }
    }

    public function bookUpdate(string $id): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $bookModel = new Book($this->database());
            $bookModel->update((int)$id, $bookModel->dataFromInput($_POST));
            Session::flash('success', 'Đã cập nhật sách thành công.');
            header('Location: /admin/books');
        } catch (Throwable $exception) {
            $bookModel = new Book($this->database());
            $data = array('title' => 'Chỉnh sửa sách');
            $data['error'] = $exception->getMessage();
            $data['book'] = $bookModel->findById((int)$id);
            $data['categories'] = (new Category($this->database()))->all();
            $this->view->render('admin/book_form', $data, 'admin');
        }
    }

    public function bookDelete(string $id): void
    {
        $this->authorize();
        Csrf::verify($_POST['_csrf'] ?? null);
        (new Book($this->database()))->delete((int)$id);
        Session::flash('success', 'Đã xóa sách thành công.');
        header('Location: /admin/books');
    }

    public function categories(): void
    {
        $this->authorize();
        $data = array('title' => 'Danh mục');
        $data['categories'] = (new Category($this->database()))->all();
        $this->view->render('admin/categories', $data, 'admin');
    }

    public function categoryStore(): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $categoryModel = new Category($this->database());
            $category = $categoryModel->dataFromInput($_POST);
            $categoryModel->create($category[0], $category[1], $category[2]);
            Session::flash('success', 'Đã thêm danh mục thành công.');
            header('Location: /admin/categories');
        } catch (Throwable $exception) {
            $this->showCategoryError($exception->getMessage());
        }
    }

    public function categoryUpdate(string $id): void
    {
        $this->authorize();
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $categoryModel = new Category($this->database());
            $category = $categoryModel->dataFromInput($_POST);
            $categoryModel->update((int)$id, $category[0], $category[1], $category[2]);
            Session::flash('success', 'Đã cập nhật danh mục thành công.');
            header('Location: /admin/categories');
        } catch (Throwable $exception) {
            $this->showCategoryError($exception->getMessage());
        }
    }

    public function categoryDelete(string $id): void
    {
        $this->authorize();
        Csrf::verify($_POST['_csrf'] ?? null);
        (new Category($this->database()))->delete((int)$id);
        Session::flash('success', 'Đã xóa danh mục thành công.');
        header('Location: /admin/categories');
    }

    private function showCategoryError(string $error): void
    {
        http_response_code(422);
        $data = array('title' => 'Danh mục');
        $data['error'] = $error;
        $data['categories'] = (new Category($this->database()))->all();
        $this->view->render('admin/categories', $data, 'admin');
    }
}
