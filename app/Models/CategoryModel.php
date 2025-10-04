<?php
namespace App\Models;

class CategoryModel extends BaseModel
{
    public function listAll(): array
    {
        return $this->pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
    }

    public function listWithTotals(): array
    {
        return $this->pdo->query("SELECT c.category_id, c.category_name, c.description, COUNT(a.article_id) AS total
                             FROM categories c
                             LEFT JOIN articles a ON a.category_id = c.category_id AND a.status='published'
                             GROUP BY c.category_id, c.category_name, c.description
                             ORDER BY c.category_name")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $s = $this->pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
        $s->execute([$id]);
        $row = $s->fetch();
        return $row ?: null;
    }

    public function create(string $name, string $description): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO categories(category_name, description) VALUES(?, ?)");
        $stmt->execute([$name, $description]);
    }

    public function update(int $id, string $name, string $description): void
    {
        $stmt = $this->pdo->prepare("UPDATE categories SET category_name=?, description=? WHERE category_id=?");
        $stmt->execute([$name, $description, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE category_id=?");
        $stmt->execute([$id]);
    }
}


