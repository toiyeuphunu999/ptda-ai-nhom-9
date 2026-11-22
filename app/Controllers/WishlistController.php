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
        if (!is_array($user)) { header('Location: /login'); return; }
        $this->view->render('shop/wishlist', [
            'title' => 'Sách yêu thích',
            'books' => (new Wishlist($this->database()))->forUser((int)$user['id']),
        ]);
    }

    public function add(string $id): void { $this->change('add', (int)$id); }
    public function remove(string $id): void { $this->change('remove', (int)$id); }

    private function change(string $action, int $bookId): void
    {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) { header('Location: /login'); return; }
        $redirect = $action === 'remove' ? '/wishlist' : '/books';
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $book = (new Book($this->database()))->findById($bookId);
            if (!$book || $book['status'] !== 'published') throw new \RuntimeException('Sách không tồn tại hoặc chưa được bán.');
            if ($action === 'add') $redirect = '/books/' . rawurlencode((string)$book['slug']);
            $wishlist = new Wishlist($this->database());
            if ($action === 'add') { $wishlist->add((int)$user['id'], $bookId); Session::flash('success', 'Đã thêm sách vào danh sách yêu thích.'); }
            else { $wishlist->remove((int)$user['id'], $bookId); Session::flash('success', 'Đã xóa sách khỏi danh sách yêu thích.'); }
        } catch (Throwable $exception) { Session::flash('failure', $exception->getMessage()); }
        header('Location: ' . $redirect);
    }
}
