<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Book;
use App\Models\Wishlist;
use Throwable;

final class WishlistController extends Controller
{
    public function index(): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) {
            header('Location: /login');
            return;
        }

        $data = array(
            'title' => 'Sách yêu thích',
            'books' => (new Wishlist($this->database()))->forUser((int)$user['id']),
        );
        $this->view->render('shop/wishlist', $data);
    }

    public function add(string $id): void
    {
        $bookId = (int)$id;
        $this->change('add', $bookId);
    }

    public function remove(string $id): void
    {
        $bookId = (int)$id;
        $this->change('remove', $bookId);
    }

    private function change(string $action, int $bookId): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) {
            header('Location: /login');
            return;
        }

        $redirect = '/books';
        if ($action === 'remove') {
            $redirect = '/wishlist';
        }

        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $bookModel = new Book($this->database());
            $book = $bookModel->findById($bookId);
            if (!$book || $book['status'] !== 'published') {
                throw new \RuntimeException('Sách không tồn tại hoặc chưa được bán.');
            }

            if ($action === 'add') {
                $redirect = '/books/' . rawurlencode((string)$book['slug']);
            }

            $wishlistModel = new Wishlist($this->database());
            $userId = (int)$user['id'];
            if ($action === 'add') {
                $wishlistModel->add($userId, $bookId);
                Session::flash('success', 'Đã thêm sách vào danh sách yêu thích.');
            } else {
                $wishlistModel->remove($userId, $bookId);
                Session::flash('success', 'Đã xóa sách khỏi danh sách yêu thích.');
            }
        } catch (Throwable $exception) {
            Session::flash('failure', $exception->getMessage());
        }
        header('Location: ' . $redirect);
    }
}
