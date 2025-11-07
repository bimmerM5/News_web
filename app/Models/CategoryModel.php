<?php
namespace App\Models;

use App\Queries\CategoryRepositoryInterface;
use App\Queries\CategoryRepository;

/**
 * Category Model - Cải thiện với Repository Pattern
 * 
 * Tuân thủ:
 * - Dependency Inversion Principle: Phụ thuộc vào Interface
 * - Single Responsibility: Chỉ xử lý business logic
 */
class CategoryModel extends BaseModel
{
    private CategoryRepositoryInterface $repository;

    /**
     * Constructor với Dependency Injection
     */
    public function __construct(?\PDO $pdo = null, ?CategoryRepositoryInterface $repository = null)
    {
        parent::__construct($pdo);
        $this->repository = $repository ?? new CategoryRepository($this->pdo);
    }

    public function listAll(): array
    {
        return $this->repository->listAll();
    }

    public function listWithTotals(): array
    {
        return $this->repository->listWithTotals();
    }

    public function find(int $id): ?array
    {
        return $this->repository->find($id);
    }

    public function create(string $name, string $description): void
    {
        $this->repository->create([
            'category_name' => $name,
            'description' => $description
        ]);
    }

    public function update(int $id, string $name, string $description): void
    {
        $this->repository->update($id, [
            'category_name' => $name,
            'description' => $description
        ]);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }

    /**
     * Kiểm tra danh mục có bài viết không
     */
    public function hasArticles(int $id): bool
    {
        return $this->repository->hasArticles($id);
    }

    /**
     * Lấy danh sách ID bài viết thuộc danh mục
     */
    public function getArticleIdsByCategory(int $id): array
    {
        return $this->repository->getArticleIdsByCategory($id);
    }

    /**
     * Lấy tất cả danh mục (theo ID)
     */
    public function listAllById(): array
    {
        return $this->repository->listAllById();
    }
}


