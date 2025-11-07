<?php
namespace App\Queries;

use App\Core\Database;

/**
 * Category Repository - Tuân thủ đầy đủ OOP principles
 * 
 * - Dependency Injection: Nhận PDO qua constructor
 * - Interface Segregation: Implement CategoryRepositoryInterface
 * - Single Responsibility: Chỉ xử lý data access cho Category
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function find(int $id): ?array
    {
        $sql = CategoryQueries::find();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findAll(): array
    {
        return $this->listAll();
    }

    public function create(array $data): int
    {
        $sql = CategoryQueries::create();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['category_name'] ?? '',
            $data['description'] ?? ''
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = CategoryQueries::update();
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['category_name'] ?? '',
            $data['description'] ?? '',
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = CategoryQueries::delete();
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function listAll(): array
    {
        $sql = CategoryQueries::listAll();
        return $this->pdo->query($sql)->fetchAll();
    }

    public function listWithTotals(): array
    {
        $sql = CategoryQueries::listWithTotals();
        return $this->pdo->query($sql)->fetchAll();
    }

    public function hasArticles(int $id): bool
    {
        $sql = CategoryQueries::hasArticles();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getArticleIdsByCategory(int $id): array
    {
        $sql = CategoryQueries::getArticleIdsByCategory();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function listAllById(): array
    {
        $sql = CategoryQueries::listAllById();
        return $this->pdo->query($sql)->fetchAll();
    }
}

