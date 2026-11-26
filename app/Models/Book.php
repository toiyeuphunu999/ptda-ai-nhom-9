<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\ViewHelper;
use App\Core\Database;
use RuntimeException;

final class Book
{
    public function __construct(private Database $database)
    {
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT books.*, categories.name AS category_name, categories.slug AS category_slug FROM books '
            . 'INNER JOIN categories ON categories.id = books.category_id '
            . 'WHERE books.slug = :slug AND books.status = :status LIMIT 1'
        );
        $statement->execute(['slug' => $slug, 'status' => 'published']);
        $book = $statement->fetch();
        return $book ?: null;
    }

    public function published(): array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT books.*, categories.name AS category_name FROM books '
            . 'INNER JOIN categories ON categories.id = books.category_id '
            . 'WHERE books.status = :status ORDER BY books.created_at DESC'
        );
        $statement->execute(['status' => 'published']);
        return $statement->fetchAll();
    }

    public function filterPublished(array $filters): array
    {
        $conditions = ['books.status = :status'];
        $parameters = ['status' => 'published'];

        if (($filters['category'] ?? '') !== '') {
            $conditions[] = 'categories.slug = :category';
            $parameters['category'] = $filters['category'];
        }
        if (($filters['author'] ?? '') !== '') {
            $conditions[] = 'books.author = :author';
            $parameters['author'] = $filters['author'];
        }
        if (($filters['min_price'] ?? null) !== null) {
            $conditions[] = 'books.price >= :min_price';
            $parameters['min_price'] = $filters['min_price'];
        }
        if (($filters['max_price'] ?? null) !== null) {
            $conditions[] = 'books.price <= :max_price';
            $parameters['max_price'] = $filters['max_price'];
        }

        $orderBy = 'books.created_at DESC, books.id DESC';
        if (($filters['sort'] ?? 'newest') === 'price_asc') {
            $orderBy = 'books.price ASC, books.title ASC';
        }
        if (($filters['sort'] ?? 'newest') === 'price_desc') {
            $orderBy = 'books.price DESC, books.title ASC';
        }
        $sql = 'SELECT books.*, categories.name AS category_name FROM books '
            . 'INNER JOIN categories ON categories.id = books.category_id '
            . 'WHERE ' . implode(' AND ', $conditions) . ' ORDER BY ' . $orderBy;
        $statement = $this->database->connection()->prepare($sql);
        $statement->execute($parameters);
        return $statement->fetchAll();
    }

    public function publishedAuthors(): array
    {
        return $this->database->connection()->query(
            "SELECT DISTINCT author FROM books WHERE status = 'published' ORDER BY author"
        )->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function byCategory(string $categorySlug): array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT books.*, categories.name AS category_name FROM books '
            . 'INNER JOIN categories ON categories.id = books.category_id '
            . 'WHERE categories.slug = :slug AND books.status = :status ORDER BY books.title'
        );
        $statement->execute(['slug' => $categorySlug, 'status' => 'published']);
        return $statement->fetchAll();
    }

    public function relatedPublished(int $categoryId, int $bookId, int $limit = 4): array
    {
        $limit = max(1, min(8, $limit));
        $statement = $this->database->connection()->prepare(
            'SELECT books.*, categories.name AS category_name FROM books '
            . 'INNER JOIN categories ON categories.id = books.category_id '
            . 'WHERE books.category_id = :category_id AND books.id <> :book_id AND books.status = :status '
            . 'ORDER BY books.created_at DESC, books.id DESC LIMIT ' . $limit
        );
        $statement->execute(['category_id'=>$categoryId,'book_id'=>$bookId,'status'=>'published']);
        return $statement->fetchAll();
    }

    public function search(string $query): array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT books.*, categories.name AS category_name FROM books '
            . 'INNER JOIN categories ON categories.id = books.category_id '
            . 'WHERE books.status = :status AND (books.title LIKE :title_query OR books.author LIKE :author_query) '
            . 'ORDER BY books.title'
        );
        $likeQuery = '%' . $query . '%';
        $statement->execute(['status' => 'published', 'title_query' => $likeQuery, 'author_query' => $likeQuery]);
        return $statement->fetchAll();
    }

    public function allForAdmin(): array
    {
        return $this->database->connection()->query(
            'SELECT books.*, categories.name AS category_name FROM books '
            . 'INNER JOIN categories ON categories.id = books.category_id ORDER BY books.id DESC'
        )->fetchAll();
    }

    public function countAll(): int
    {
        return (int)$this->database->connection()->query('SELECT COUNT(*) FROM books')->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->database->connection()->prepare('SELECT * FROM books WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $book = $statement->fetch();
        return $book ?: null;
    }

    public function create(array $data): int
    {
        $data['slug'] = $this->uniqueSlug((string)$data['title']);
        $statement = $this->database->connection()->prepare(
            'INSERT INTO books (category_id, title, slug, author, publisher, price, original_price, description, preview_content, stock_quantity, status) '
            . 'VALUES (:category_id, :title, :slug, :author, :publisher, :price, :original_price, :description, :preview_content, :stock_quantity, :status)'
        );
        $statement->execute($data);
        return (int)$this->database->connection()->lastInsertId();
    }

    public function dataFromInput(array $input): array
    {
        $title = trim((string)($input['title'] ?? ''));
        $author = trim((string)($input['author'] ?? ''));
        $priceInput = trim((string)($input['price'] ?? ''));
        $price = filter_var($priceInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $original = trim((string)($input['original_price'] ?? ''));
        $originalValue = $original === '' ? null : filter_var($original, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $stock = filter_var($input['stock_quantity'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($price === false || $categoryId === false || $stock === false || $title === '' || mb_strlen($title) > 190 || $author === '' || mb_strlen($author) > 190 || ($original !== '' && $originalValue === false) || ($originalValue !== null && $originalValue < $price)) {
            throw new RuntimeException('Vui lòng kiểm tra tên sách, tác giả, danh mục và giá sách.');
        }
        if (mb_strlen((string)($input['publisher'] ?? '')) > 190 || mb_strlen((string)($input['description'] ?? '')) > 10000 || mb_strlen((string)($input['preview_content'] ?? '')) > 50000) {
            throw new RuntimeException('Thông tin sách vượt quá độ dài cho phép.');
        }
        $description = ViewHelper::richText(trim((string)($input['description'] ?? '')));
        $previewContent = ViewHelper::richText(trim((string)($input['preview_content'] ?? '')));
        return [
            'category_id' => $categoryId,
            'title' => $title,
            'author' => $author,
            'publisher' => trim((string)($input['publisher'] ?? '')) ?: null,
            'price' => $price,
            'original_price' => $originalValue === null || $originalValue === false ? null : $originalValue,
            'description' => $description !== '' ? $description : null,
            'preview_content' => $previewContent !== '' ? $previewContent : null,
            'stock_quantity' => $stock,
            'status' => in_array($input['status'] ?? 'draft', ['draft', 'published', 'hidden'], true) ? $input['status'] : 'draft',
        ];
    }

    public function update(int $id, array $data): void
    {
        $data['slug'] = $this->uniqueSlug((string)$data['title'], $id);
        $data['id'] = $id;
        $statement = $this->database->connection()->prepare(
            'UPDATE books SET category_id = :category_id, title = :title, slug = :slug, author = :author, '
            . 'publisher = :publisher, price = :price, original_price = :original_price, description = :description, preview_content = :preview_content, '
            . 'stock_quantity = :stock_quantity, status = :status WHERE id = :id'
        );
        $statement->execute($data);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $replacements = [
            'à'=>'a','á'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a','ă'=>'a','ắ'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a','ặ'=>'a','â'=>'a','ấ'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a',
            'À'=>'A','Á'=>'A','Ả'=>'A','Ã'=>'A','Ạ'=>'A','Ă'=>'A','Ắ'=>'A','Ằ'=>'A','Ẳ'=>'A','Ẵ'=>'A','Ặ'=>'A','Â'=>'A','Ấ'=>'A','Ầ'=>'A','Ẩ'=>'A','Ẫ'=>'A','Ậ'=>'A',
            'đ'=>'d','Đ'=>'D','è'=>'e','é'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e','ê'=>'e','ế'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e',
            'È'=>'E','É'=>'E','Ẻ'=>'E','Ẽ'=>'E','Ẹ'=>'E','Ê'=>'E','Ế'=>'E','Ề'=>'E','Ể'=>'E','Ễ'=>'E','Ệ'=>'E',
            'ì'=>'i','í'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i','Ì'=>'I','Í'=>'I','Ỉ'=>'I','Ĩ'=>'I','Ị'=>'I',
            'ò'=>'o','ó'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o','ô'=>'o','ố'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o','ơ'=>'o','ớ'=>'o','ờ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o',
            'Ò'=>'O','Ó'=>'O','Ỏ'=>'O','Õ'=>'O','Ọ'=>'O','Ô'=>'O','Ố'=>'O','Ồ'=>'O','Ổ'=>'O','Ỗ'=>'O','Ộ'=>'O','Ơ'=>'O','Ớ'=>'O','Ờ'=>'O','Ở'=>'O','Ỡ'=>'O','Ợ'=>'O',
            'ù'=>'u','ú'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u','ư'=>'u','ứ'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u','Ù'=>'U','Ú'=>'U','Ủ'=>'U','Ũ'=>'U','Ụ'=>'U','Ư'=>'U','Ứ'=>'U','Ừ'=>'U','Ử'=>'U','Ữ'=>'U','Ự'=>'U',
            'ỳ'=>'y','ý'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y','Ỳ'=>'Y','Ý'=>'Y','Ỷ'=>'Y','Ỹ'=>'Y','Ỵ'=>'Y'
        ];
        $slugBase = strtolower(strtr(trim($title), $replacements));
        $slugBase = trim((string)preg_replace('/[^a-z0-9]+/', '-', $slugBase), '-');
        $slugBase = $slugBase !== '' ? $slugBase : 'book';
        $slug = $slugBase;
        $suffix = 2;
        do {
            $sql = 'SELECT id FROM books WHERE slug = :slug' . ($ignoreId !== null ? ' AND id <> :id' : '') . ' LIMIT 1';
            $statement = $this->database->connection()->prepare($sql);
            $params = ['slug' => $slug];
            if ($ignoreId !== null) $params['id'] = $ignoreId;
            $statement->execute($params);
            if (!$statement->fetch()) return $slug;
            $slug = $slugBase . '-' . $suffix++;
        } while (true);
    }

    public function delete(int $id): void
    {
        $statement = $this->database->connection()->prepare('DELETE FROM books WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
