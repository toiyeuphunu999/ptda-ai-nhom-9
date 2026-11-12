<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Book;
use App\Models\Category;
use App\Models\Review;
use App\Core\Csrf;
use App\Core\Session;
use Throwable;

final class BookController extends Controller
{
    private function bookModel(): Book { return new Book(new Database(require dirname(__DIR__, 2) . '/config/database.php')); }
    private function categoryModel(): Category { return new Category(new Database(require dirname(__DIR__, 2) . '/config/database.php')); }

    public function index(): void
    {
        $category = trim((string)($_GET['category'] ?? ''));
        $author = trim((string)($_GET['author'] ?? ''));
        $requestedSort = (string)($_GET['sort'] ?? 'newest');
        $sort = in_array($requestedSort, ['newest', 'price_asc', 'price_desc'], true)
            ? $requestedSort : 'newest';
        $minPrice = filter_var($_GET['min_price'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $maxPrice = filter_var($_GET['max_price'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($minPrice !== false && $maxPrice !== false && $minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }
        $filters = compact('category', 'author', 'sort');
        $filters['min_price'] = $minPrice === false ? null : $minPrice;
        $filters['max_price'] = $maxPrice === false ? null : $maxPrice;
        $this->view->render('shop/books', [
            'title' => 'Tất cả sách',
            'books' => $this->bookModel()->filterPublished($filters),
            'categories' => $this->categoryModel()->all(),
            'authors' => $this->bookModel()->publishedAuthors(),
            'filters' => $filters,
        ]);
    }
    public function detail(string $slug): void {
        $book = $this->bookModel()->findBySlug($slug);
        if (!$book) { http_response_code(404); $this->view->render('errors/404', ['title'=>'Không tìm thấy sách']); return; }
        Session::start();
        $database = new Database(require dirname(__DIR__, 2) . '/config/database.php');
        $reviewModel = new Review($database);
        $user = Session::get('user');
        $canReview = is_array($user) && $reviewModel->canReview((int)$user['id'], (int)$book['id']) && !$reviewModel->hasReview((int)$user['id'], (int)$book['id']);
        $this->view->render('shop/book_detail', ['title'=>$book['title'],'book'=>$book,'reviews'=>$reviewModel->forBook((int)$book['id']),'reviewSummary'=>$reviewModel->summary((int)$book['id']),'canReview'=>$canReview,'relatedBooks'=>(new Book($database))->relatedPublished((int)$book['category_id'], (int)$book['id'])]);
    }
    public function reviewSubmit(string $id): void {
        Session::start();
        $user = Session::get('user');
        if (!is_array($user)) { header('Location: /login'); return; }
        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $book = $this->bookModel()->findById((int)$id);
            $reviewModel = new Review(new Database(require dirname(__DIR__, 2) . '/config/database.php'));
            if (!$book || $book['status'] !== 'published' || !$reviewModel->canReview((int)$user['id'], (int)$id)) throw new \RuntimeException('Chỉ khách hàng đã mua sách mới có thể đánh giá.');
            if ($reviewModel->hasReview((int)$user['id'], (int)$id)) throw new \RuntimeException('Bạn đã đánh giá sách này rồi.');
            $reviewModel->save((int)$user['id'], (int)$id, (int)($_POST['rating'] ?? 0), (string)($_POST['comment'] ?? ''));
            header('Location: /books/' . rawurlencode((string)$book['slug']) . '#reviews');
        } catch (Throwable $exception) {
            http_response_code(422);
            $book = $this->bookModel()->findById((int)$id);
            $this->view->render('shop/book_detail', ['title'=>$book['title'] ?? 'Đánh giá','book'=>$book,'reviews'=>[],'reviewSummary'=>['average'=>0,'total'=>0],'canReview'=>true,'error'=>$exception->getMessage()]);
        }
    }
    public function category(string $slug): void {
        $books = $this->bookModel()->byCategory($slug);
        $category = $this->categoryModel()->findBySlug($slug);
        $this->view->render('shop/category', ['title'=>$category['name'] ?? 'Danh mục','books'=>$books,'category'=>$category ?: null]);
    }
    public function search(): void {
        $query = trim((string)($_GET['q'] ?? ''));
        $books = $query === '' ? $this->bookModel()->published() : $this->bookModel()->search($query);
        $this->view->render('shop/search', ['title'=>'Tìm kiếm','books'=>$books,'query'=>$query]);
    }
}
