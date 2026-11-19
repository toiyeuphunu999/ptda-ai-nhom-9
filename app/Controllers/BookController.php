<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Review;
use App\Models\MemberPoint;
use App\Core\Csrf;
use App\Core\Session;
use Throwable;

final class BookController extends Controller
{
    private function bookModel(): Book
    {
        return new Book($this->database());
    }

    private function categoryModel(): Category
    {
        return new Category($this->database());
    }

    // Danh sách sách và bộ lọc
    public function index(): void
    {
        $category = trim((string)($_GET['category'] ?? ''));

        $author = trim((string)($_GET['author'] ?? ''));

        $publisher = trim((string)($_GET['publisher'] ?? ''));

        $requestedSort = (string)($_GET['sort'] ?? 'newest');

        $sort = in_array(
            $requestedSort,
            ['newest', 'price_asc', 'price_desc'],
            true
        ) ? $requestedSort : 'newest';

        $minPrice = filter_var(
            $_GET['min_price'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0]]
        );

        $maxPrice = filter_var(
            $_GET['max_price'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0]]
        );

        $publishedYear = filter_var(
            $_GET['published_year'] ?? null,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1000,
                    'max_range' => (int)date('Y') + 1
                ]
            ]
        );

        if (
            $minPrice !== false &&
            $maxPrice !== false &&
            $minPrice !== null &&
            $maxPrice !== null &&
            $minPrice > $maxPrice
        ) {
            [$minPrice, $maxPrice] = [
                $maxPrice,
                $minPrice
            ];
        }

        $filters = [
            'category' => $category,
            'author' => $author,
            'publisher' => $publisher,
            'published_year' => (
                $publishedYear === false ||
                $publishedYear === null
            ) ? null : $publishedYear,
            'min_price' => $minPrice === false ? null : $minPrice,
            'max_price' => $maxPrice === false ? null : $maxPrice,
            'sort' => $sort
        ];

        $bookModel = $this->bookModel();

        $this->view->render('shop/books', [
            'title' => 'Tất cả sách',

            'books' => $bookModel->filterPublished($filters),

            'categories' => $this->categoryModel()->all(),

            'authors' => $bookModel->publishedAuthors(),

            'publishers' => $bookModel->publishedPublishers(),

            'years' => $bookModel->publishedYears(),

            'filters' => $filters
        ]);
    }

    // Chi tiết sách
    public function detail(string $slug): void
    {
        $book = $this->bookModel()->findBySlug($slug);

        if (!$book) {
            http_response_code(404);

            $this->view->render('errors/error', [
                'title' => 'Không tìm thấy sách',
                'errorCode' => '404',
                'errorTitle' => 'Không tìm thấy sách',
                'errorMessage' => 'Tựa sách này không tồn tại hoặc đã được gỡ khỏi cửa hàng.',
                'errorLabel' => 'Bạn có thể quay lại danh sách để tiếp tục tìm một cuốn sách khác.',
                'primaryUrl' => '/books',
                'primaryLabel' => 'Xem danh sách sách'
            ]);

            return;
        }

        Session::start();

        $database = $this->database();

        $reviewModel = new Review($database);

        $user = Session::get('user');

        $canReview = is_array($user)
            && $reviewModel->canReview(
                (int)$user['id'],
                (int)$book['id']
            )
            && !$reviewModel->hasReview(
                (int)$user['id'],
                (int)$book['id']
            );

        $this->view->render('shop/book_detail', [
            'title' => $book['title'],
            'book' => $book,
            'reviews' => $reviewModel->forBook((int)$book['id']),
            'reviewSummary' => $reviewModel->summary((int)$book['id']),
            'canReview' => $canReview,
            'relatedBooks' => (new Book($database))->relatedPublished(
                (int)$book['category_id'],
                (int)$book['id']
            )
        ]);
    }

    // Gửi đánh giá
    public function reviewSubmit(string $id): void
    {
        Session::start();

        $user = Session::get('user');

        if (!is_array($user)) {
            header('Location: /login');
            return;
        }

        try {
            Csrf::verify($_POST['_csrf'] ?? null);

            $book = $this->bookModel()->findById((int)$id);

            $reviewModel = new Review($this->database());

            if (
                !$book ||
                $book['status'] !== 'published' ||
                !$reviewModel->canReview((int)$user['id'], (int)$id)
            ) {
                throw new \RuntimeException(
                    'Chỉ khách hàng đã mua sách mới có thể đánh giá.'
                );
            }

            if ($reviewModel->hasReview((int)$user['id'], (int)$id)) {
                throw new \RuntimeException(
                    'Bạn đã đánh giá sách này rồi.'
                );
            }

            $reviewId = $reviewModel->save(
                (int)$user['id'],
                (int)$id,
                (int)($_POST['rating'] ?? 0),
                (string)($_POST['comment'] ?? '')
            );

            (new MemberPoint($this->database()))
                ->earnForReview((int)$user['id'], $reviewId);

            header(
                'Location: /books/' .
                rawurlencode((string)$book['slug']) .
                '#reviews'
            );

        } catch (Throwable $exception) {
            http_response_code(422);

            $book = $this->bookModel()->findById((int)$id);

            $this->view->render('shop/book_detail', [
                'title' => $book['title'] ?? 'Đánh giá',
                'book' => $book,
                'reviews' => [],
                'reviewSummary' => [
                    'average' => 0,
                    'total' => 0
                ],
                'canReview' => true,
                'error' => $exception->getMessage()
            ]);
        }
    }

    // Danh mục sách
    public function category(string $slug): void
    {
        $books = $this->bookModel()->byCategory($slug);

        $category = $this->categoryModel()->findBySlug($slug);

        $this->view->render('shop/category', [
            'title' => $category['name'] ?? 'Danh mục',
            'books' => $books,
            'category' => $category ?: null
        ]);
    }

    // Tìm kiếm
    public function search(): void
    {
        $query = trim((string)($_GET['q'] ?? ''));

        $books = $query === ''
            ? $this->bookModel()->published()
            : $this->bookModel()->search($query);

        $this->view->render('shop/search', [
            'title' => 'Tìm kiếm',
            'books' => $books,
            'query' => $query
        ]);
    }
}