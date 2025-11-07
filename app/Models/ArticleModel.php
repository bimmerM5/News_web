<?php
namespace App\Models;

use App\Queries\ArticleRepositoryInterface;
use App\Queries\ArticleRepository;

/**
 * Article Model - Cải thiện với Repository Pattern
 * 
 * Tuân thủ:
 * - Dependency Inversion Principle: Phụ thuộc vào Interface, không phải concrete class
 * - Single Responsibility: Chỉ xử lý business logic, không xử lý SQL
 * - Open/Closed: Có thể extend mà không modify
 */
class ArticleModel extends BaseModel
{
    private ArticleRepositoryInterface $repository;

    /**
     * Constructor với Dependency Injection
     * Cho phép inject Repository (hữu ích cho testing)
     */
    public function __construct(?\PDO $pdo = null, ?ArticleRepositoryInterface $repository = null)
    {
        parent::__construct($pdo);
        $this->repository = $repository ?? new ArticleRepository($this->pdo);
    }

    /**
     * Lấy danh sách bài viết đã xuất bản với phân trang
     * 
     * @return array [articles, total]
     */
    public function getPublishedArticles(int $page, int $perPage, ?int $categoryId = null): array
    {
        $articles = $this->repository->getPublishedArticles($page, $perPage, $categoryId);
        $total = $this->repository->countPublishedArticles($categoryId);
        
        return [$articles, $total];
    }

    /**
     * Tăng lượt xem bài viết
     */
    public function incrementViews(int $id): bool
    {
        return $this->repository->incrementViews($id);
    }

    /**
     * Thêm lượt xem (với user tracking)
     */
    public function addView(int $articleId, ?int $userId = null): bool
    {
        return $this->repository->addView($articleId, $userId);
    }

    /**
     * Lấy chi tiết bài viết với nội dung và media
     */
    public function getByIdWithDetails(int $id): ?array
    {
        return $this->repository->getByIdWithDetails($id);
    }

    /**
     * Lấy bài viết theo danh mục
     */
    public function getByCategory(int $categoryId, int $page, int $perPage): array
    {
        $articles = $this->repository->getPublishedArticles($page, $perPage, $categoryId);
        $total = $this->repository->countPublishedArticles($categoryId);
        
        return [$articles, $total];
    }

    /**
     * Tìm kiếm bài viết
     */
    public function searchArticles(string $keyword, int $page, int $perPage): array
    {
        $articles = $this->repository->searchArticles($keyword, $page, $perPage);
        
        // Count search results
        $sql = \App\Queries\ArticleQueries::countSearchResults();
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':kw1', '%' . $keyword . '%');
        $stmt->bindValue(':kw2', '%' . $keyword . '%');
        $stmt->execute();
        $total = (int)$stmt->fetchColumn();
        
        return [$articles, $total];
    }

    /**
     * Lấy bài viết của user
     */
    public function getUserArticles(int $userId): array
    {
        return $this->repository->getUserArticles($userId);
    }
}


