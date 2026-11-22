<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Book;
use App\Models\Category;
use App\Models\MemberPoint;
use App\Models\Review;
use Throwable;

class BookController extends Controller
{
    private function getBookModel(): Book
    {
        return new Book($this->database());
    }

    private function getCategoryModel(): Category
    {
        return new Category($this->database());
    }

    public function index(): void
    {
        $category = trim((string)($_GET['category'] ?? ''));
        $author = trim((string)($_GET['author'] ?? ''));
        $sort = (string)($_GET['sort'] ?? 'newest');

        if ($sort !== 'price_asc' && $sort !== 'price_desc') {
            $sort = 'newest';
        }

        $minPrice = $this->getPrice($_GET['min_price'] ?? null);
        $maxPrice = $this->getPrice($_GET['max_price'] ?? null);

        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            $oldMinPrice = $minPrice;
            $minPrice = $maxPrice;
            $maxPrice = $oldMinPrice;
        }

        $filters = array();
        $filters['category'] = $category;
        $filters['author'] = $author;
        $filters['sort'] = $sort;
        $filters['min_price'] = $minPrice;
        $filters['max_price'] = $maxPrice;

        $bookModel = $this->getBookModel();
        $data = array();
        $data['title'] = 'Tất cả sách';
        $data['books'] = $bookModel->filterPublished($filters);
        $data['categories'] = $this->getCategoryModel()->all();
        $data['authors'] = $bookModel->publishedAuthors();
        $data['filters'] = $filters;
        $this->view->render('shop/books', $data);
    }

    public function detail(string $slug): void
    {
        $bookModel = $this->getBookModel();
        $book = $bookModel->findBySlug($slug);

        if (!$book) {
            $this->showNotFoundBook();
            return;
        }

        Session::start();
        $reviewModel = new Review($this->database());
        $user = Session::get('user');
        $canReview = false;

        if (is_array($user)) {
            $userId = (int)$user['id'];
            $bookId = (int)$book['id'];
            $canReview = $reviewModel->canReview($userId, $bookId);
            if ($reviewModel->hasReview($userId, $bookId)) {
                $canReview = false;
            }
        }

        $data = array();
        $data['title'] = $book['title'];
        $data['book'] = $book;
        $data['reviews'] = $reviewModel->forBook((int)$book['id']);
        $data['reviewSummary'] = $reviewModel->summary((int)$book['id']);
        $data['canReview'] = $canReview;
        $data['relatedBooks'] = $bookModel->relatedPublished((int)$book['category_id'], (int)$book['id']);
        $this->view->render('shop/book_detail', $data);
    }

    public function reviewSubmit(string $id): void
    {
        Session::start();
        $user = Session::get('user');

        if (!is_array($user)) {
            header('Location: /login');
            return;
        }

        $bookModel = $this->getBookModel();
        $book = $bookModel->findById((int)$id);

        try {
            Csrf::verify($_POST['_csrf'] ?? null);
            $reviewModel = new Review($this->database());
            $userId = (int)$user['id'];

            if (!$book || $book['status'] !== 'published') {
                throw new \RuntimeException('Sách không tồn tại hoặc chưa được bán.');
            }
            if (!$reviewModel->canReview($userId, (int)$id)) {
                throw new \RuntimeException('Chỉ khách hàng đã mua sách mới có thể đánh giá.');
            }
            if ($reviewModel->hasReview($userId, (int)$id)) {
                throw new \RuntimeException('Bạn đã đánh giá sách này rồi.');
            }

            $reviewId = $reviewModel->save(
                $userId,
                (int)$id,
                (int)($_POST['rating'] ?? 0),
                (string)($_POST['comment'] ?? '')
            );
            $pointModel = new MemberPoint($this->database());
            $pointModel->earnForReview($userId, $reviewId);
            header('Location: /books/' . rawurlencode((string)$book['slug']) . '#reviews');
        } catch (Throwable $exception) {
            http_response_code(422);
            $data = array();
            $data['title'] = $book['title'] ?? 'Đánh giá';
            $data['book'] = $book;
            $data['reviews'] = array();
            $data['reviewSummary'] = array('average' => 0, 'total' => 0);
            $data['canReview'] = true;
            $data['error'] = $exception->getMessage();
            $this->view->render('shop/book_detail', $data);
        }
    }

    public function category(string $slug): void
    {
        $categoryModel = $this->getCategoryModel();
        $category = $categoryModel->findBySlug($slug);
        $bookModel = $this->getBookModel();

        $data = array();
        $data['title'] = $category ? $category['name'] : 'Danh mục';
        $data['books'] = $bookModel->byCategory($slug);
        $data['category'] = $category;
        $this->view->render('shop/category', $data);
    }

    public function search(): void
    {
        $query = trim((string)($_GET['q'] ?? ''));
        $bookModel = $this->getBookModel();

        if ($query === '') {
            $books = $bookModel->published();
        } else {
            $books = $bookModel->search($query);
        }

        $data = array();
        $data['title'] = 'Tìm kiếm';
        $data['books'] = $books;
        $data['query'] = $query;
        $this->view->render('shop/search', $data);
    }

    private function getPrice($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_numeric($value) || (int)$value < 0) {
            return null;
        }
        return (int)$value;
    }

    private function showNotFoundBook(): void
    {
        http_response_code(404);
        $data = array();
        $data['title'] = 'Không tìm thấy sách';
        $data['errorCode'] = '404';
        $data['errorTitle'] = 'Không tìm thấy sách';
        $data['errorMessage'] = 'Tựa sách này không tồn tại hoặc đã được gỡ khỏi cửa hàng.';
        $data['errorLabel'] = 'Bạn có thể quay lại danh sách sách.';
        $data['primaryUrl'] = '/books';
        $data['primaryLabel'] = 'Xem danh sách sách';
        $this->view->render('errors/error', $data);
    }
}
