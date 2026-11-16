<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Helpers\SlugHelper;

final class Category
{
    public function __construct(private Database $database)
    {
    }

    public function all(): array
    {
        return $this->database->connection()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
    }

    public function countAll(): int
    {
        return (int)$this->database->connection()->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->database->connection()->prepare('SELECT * FROM categories WHERE slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $category = $statement->fetch();
        return $category ?: null;
    }

    public function create(string $name, string $slug, ?string $description): int
    {
        $slug = $this->uniqueSlug($name);
        $statement = $this->database->connection()->prepare(
            'INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)'
        );
        $statement->execute(compact('name', 'slug', 'description'));
        return (int)$this->database->connection()->lastInsertId();
    }

    public function update(int $id, string $name, string $slug, ?string $description): void
    {
        $slug = $this->uniqueSlug($name, $id);
        $statement = $this->database->connection()->prepare(
            'UPDATE categories SET name = :name, slug = :slug, description = :description WHERE id = :id'
        );
        $statement->execute(compact('id', 'name', 'slug', 'description'));
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = SlugHelper::fromText($name);
        $slug = $base;
        $suffix = 2;
        do {
            $sql = 'SELECT id FROM categories WHERE slug = :slug' . ($ignoreId !== null ? ' AND id <> :id' : '') . ' LIMIT 1';
            $statement = $this->database->connection()->prepare($sql);
            $params = ['slug' => $slug];
            if ($ignoreId !== null) $params['id'] = $ignoreId;
            $statement->execute($params);
            if (!$statement->fetch()) return $slug;
            $slug = $base . '-' . $suffix++;
        } while (true);
    }

    public function delete(int $id): void
    {
        $statement = $this->database->connection()->prepare('DELETE FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
