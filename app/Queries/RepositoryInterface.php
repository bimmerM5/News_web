<?php
namespace App\Queries;

/**
 * Interface cho Repository Pattern
 * Đảm bảo tính đa hình (Polymorphism) trong OOP
 * Tuân thủ Dependency Inversion Principle (SOLID)
 */
interface RepositoryInterface
{
    public function find(int $id): ?array;
    public function findAll(): array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}

/**
 * Interface cho Article Repository
 * Đảm bảo tính đa hình và abstraction
 */
interface ArticleRepositoryInterface extends RepositoryInterface
{
    public function getPublishedArticles(int $page, int $perPage, ?int $categoryId = null): array;
    public function getByIdWithDetails(int $id): ?array;
    public function getContent(int $id): string;
    public function getMedia(int $id): array;
    public function countPublishedArticles(?int $categoryId = null): int;
    public function searchArticles(string $keyword, int $page, int $perPage): array;
    public function getUserArticles(int $userId): array;
    public function incrementViews(int $id): bool;
    public function addView(int $articleId, ?int $userId = null): bool;
}

/**
 * Interface cho Category Repository
 */
interface CategoryRepositoryInterface extends RepositoryInterface
{
    public function listAll(): array;
    public function listWithTotals(): array;
    public function hasArticles(int $id): bool;
    public function getArticleIdsByCategory(int $id): array;
}

