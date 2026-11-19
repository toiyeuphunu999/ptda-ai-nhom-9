<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Book
{
    public function __construct(
        private Database $database
    ) {
    }

    // Tìm sách theo slug
    public function findBySlug(string $slug): ?array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT books.*, categories.name AS category_name,
                    categories.slug AS category_slug
             FROM books
             INNER JOIN categories ON categories.id = books.category_id
             WHERE books.slug = :slug
             AND books.status = :status
             LIMIT 1'
        );

        $statement->execute([
            'slug' => $slug,
            'status' => 'published'
        ]);

        return $statement->fetch() ?: null;
    }

    // Lấy tất cả sách đang bán
    public function published(): array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT books.*, categories.name AS category_name
             FROM books
             INNER JOIN categories ON categories.id = books.category_id
             WHERE books.status = :status
             ORDER BY books.created_at DESC'
        );

        $statement->execute([
            'status' => 'published'
        ]);

        return $statement->fetchAll();
    }

    // Lọc sách
    public function filterPublished(array $filters): array
    {
        $conditions = [
            'books.status = :status'
        ];

        $parameters = [
            'status' => 'published'
        ];

        // Lọc danh mục
        if (($filters['category'] ?? '') !== '') {
            $conditions[] = 'categories.slug = :category';
            $parameters['category'] = $filters['category'];
        }

        // Lọc tác giả
        if (($filters['author'] ?? '') !== '') {
            $conditions[] = 'books.author = :author';
            $parameters['author'] = $filters['author'];
        }

        // Lọc nhà xuất bản
        if (($filters['publisher'] ?? '') !== '') {
            $conditions[] = 'books.publisher = :publisher';
            $parameters['publisher'] = $filters['publisher'];
        }

        // Lọc năm xuất bản
        if (!empty($filters['published_year'])) {
            $conditions[] = 'books.published_year = :published_year';

            $parameters['published_year'] =
                (int)$filters['published_year'];
        }

        // Lọc giá thấp nhất
        if (($filters['min_price'] ?? null) !== null) {
            $conditions[] = 'books.price >= :min_price';

            $parameters['min_price'] =
                (int)$filters['min_price'];
        }

        // Lọc giá cao nhất
        if (($filters['max_price'] ?? null) !== null) {
            $conditions[] = 'books.price <= :max_price';

            $parameters['max_price'] =
                (int)$filters['max_price'];
        }

        // Sắp xếp
        $orderBy = match ($filters['sort'] ?? 'newest') {
            'price_asc' => 'books.price ASC, books.title ASC',
            'price_desc' => 'books.price DESC, books.title ASC',
            default => 'books.created_at DESC, books.id DESC'
        };

        $sql = 'SELECT books.*, categories.name AS category_name
                FROM books
                INNER JOIN categories
                ON categories.id = books.category_id
                WHERE ' . implode(' AND ', $conditions) .
                ' ORDER BY ' . $orderBy;

        $statement = $this->database->connection()->prepare($sql);

        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    // Danh sách tác giả
    public function publishedAuthors(): array
    {
        return $this->database->connection()->query(
            "SELECT DISTINCT author
             FROM books
             WHERE status = 'published'
             AND author IS NOT NULL
             AND author <> ''
             ORDER BY author"
        )->fetchAll(\PDO::FETCH_COLUMN);
    }

    // Danh sách nhà xuất bản
    public function publishedPublishers(): array
    {
        return $this->database->connection()->query(
            "SELECT DISTINCT publisher
             FROM books
             WHERE status = 'published'
             AND publisher IS NOT NULL
             AND publisher <> ''
             ORDER BY publisher"
        )->fetchAll(\PDO::FETCH_COLUMN);
    }

    // Danh sách năm xuất bản
    public function publishedYears(): array
    {
        return $this->database->connection()->query(
            "SELECT DISTINCT published_year
             FROM books
             WHERE status = 'published'
             AND published_year IS NOT NULL
             ORDER BY published_year DESC"
        )->fetchAll(\PDO::FETCH_COLUMN);
    }

    // Lấy sách theo danh mục
    public function byCategory(string $categorySlug): array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT books.*, categories.name AS category_name
             FROM books
             INNER JOIN categories
             ON categories.id = books.category_id
             WHERE categories.slug = :slug
             AND books.status = :status
             ORDER BY books.title'
        );

        $statement->execute([
            'slug' => $categorySlug,
            'status' => 'published'
        ]);

        return $statement->fetchAll();
    }

    // Lấy sách liên quan
    public function relatedPublished(
        int $categoryId,
        int $bookId,
        int $limit = 4
    ): array {
        $limit = max(1, min(8, $limit));

        $statement = $this->database->connection()->prepare(
            'SELECT books.*, categories.name AS category_name
             FROM books
             INNER JOIN categories
             ON categories.id = books.category_id
             WHERE books.category_id = :category_id
             AND books.id <> :book_id
             AND books.status = :status
             ORDER BY books.created_at DESC, books.id DESC
             LIMIT ' . $limit
        );

        $statement->execute([
            'category_id' => $categoryId,
            'book_id' => $bookId,
            'status' => 'published'
        ]);

        return $statement->fetchAll();
    }

    // Tìm kiếm sách
    public function search(string $query): array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT books.*, categories.name AS category_name
             FROM books
             INNER JOIN categories
             ON categories.id = books.category_id
             WHERE books.status = :status
             AND (
                 books.title LIKE :title_query
                 OR books.author LIKE :author_query
             )
             ORDER BY books.title'
        );

        $likeQuery = '%' . $query . '%';

        $statement->execute([
            'status' => 'published',
            'title_query' => $likeQuery,
            'author_query' => $likeQuery
        ]);

        return $statement->fetchAll();
    }

    // Lấy tất cả sách cho Admin
    public function allForAdmin(): array
    {
        return $this->database->connection()->query(
            'SELECT books.*, categories.name AS category_name
             FROM books
             INNER JOIN categories
             ON categories.id = books.category_id
             ORDER BY books.id DESC'
        )->fetchAll();
    }

    // Đếm sách
    public function countAll(): int
    {
        return (int)$this->database->connection()
            ->query('SELECT COUNT(*) FROM books')
            ->fetchColumn();
    }

    // Tìm sách theo ID
    public function findById(int $id): ?array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT * FROM books WHERE id = :id LIMIT 1'
        );

        $statement->execute([
            'id' => $id
        ]);

        return $statement->fetch() ?: null;
    }

    // Thêm sách
    public function create(array $data): int
    {
        $data['slug'] = $this->uniqueSlug(
            (string)$data['title']
        );

        $statement = $this->database->connection()->prepare(
            'INSERT INTO books (
                category_id,
                title,
                slug,
                author,
                publisher,
                published_year,
                price,
                original_price,
                description,
                stock_quantity,
                status
            ) VALUES (
                :category_id,
                :title,
                :slug,
                :author,
                :publisher,
                :published_year,
                :price,
                :original_price,
                :description,
                :stock_quantity,
                :status
            )'
        );

        $statement->execute($data);

        return (int)$this->database->connection()->lastInsertId();
    }

    // Cập nhật sách
    public function update(int $id, array $data): void
    {
        $data['slug'] = $this->uniqueSlug(
            (string)$data['title'],
            $id
        );

        $data['id'] = $id;

        $statement = $this->database->connection()->prepare(
            'UPDATE books SET
                category_id = :category_id,
                title = :title,
                slug = :slug,
                author = :author,
                publisher = :publisher,
                published_year = :published_year,
                price = :price,
                original_price = :original_price,
                description = :description,
                stock_quantity = :stock_quantity,
                status = :status
             WHERE id = :id'
        );

        $statement->execute($data);
    }

    // Tạo slug không trùng
    private function uniqueSlug(
        string $title,
        ?int $ignoreId = null
    ): string {
        $replacements = [
            'à'=>'a','á'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a',
            'ă'=>'a','ắ'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a','ặ'=>'a',
            'â'=>'a','ấ'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a',
            'đ'=>'d','Đ'=>'D',
            'è'=>'e','é'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e',
            'ê'=>'e','ế'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e',
            'ì'=>'i','í'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i',
            'ò'=>'o','ó'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o',
            'ô'=>'o','ố'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o',
            'ơ'=>'o','ớ'=>'o','ờ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o',
            'ù'=>'u','ú'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u',
            'ư'=>'u','ứ'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u',
            'ỳ'=>'y','ý'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y'
        ];

        $slugBase = strtolower(
            strtr(trim($title), $replacements)
        );

        $slugBase = trim(
            (string)preg_replace('/[^a-z0-9]+/', '-', $slugBase),
            '-'
        );

        $slugBase = $slugBase !== '' ? $slugBase : 'book';

        $slug = $slugBase;
        $suffix = 2;

        do {
            $sql = 'SELECT id FROM books
                    WHERE slug = :slug';

            if ($ignoreId !== null) {
                $sql .= ' AND id <> :id';
            }

            $sql .= ' LIMIT 1';

            $statement = $this->database->connection()->prepare($sql);

            $params = [
                'slug' => $slug
            ];

            if ($ignoreId !== null) {
                $params['id'] = $ignoreId;
            }

            $statement->execute($params);

            if (!$statement->fetch()) {
                return $slug;
            }

            $slug = $slugBase . '-' . $suffix++;

        } while (true);
    }

    // Xóa sách
    public function delete(int $id): void
    {
        $statement = $this->database->connection()->prepare(
            'DELETE FROM books WHERE id = :id'
        );

        $statement->execute([
            'id' => $id
        ]);
    }
}