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

    /**
     * Toggle like cho bài viết
     */
    public function toggleLike(int $articleId, int $userId): void
    {
        $sql = \App\Queries\AdminQueries::toggleLike();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$articleId, $userId]);
    }

    /**
     * Lấy tất cả bài viết cho admin (không phân biệt status)
     */
    public function getAllForAdmin(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Lấy bài viết theo ID (đơn giản)
     */
    public function findById(int $id): ?array
    {
        return $this->repository->find($id);
    }

    /**
     * Lấy nội dung bài viết
     */
    public function getContent(int $id): string
    {
        return $this->repository->getContent($id);
    }

    /**
     * Lấy media của bài viết
     */
    public function getMedia(int $id): array
    {
        return $this->repository->getMedia($id);
    }

    /**
     * Tạo bài viết mới (admin)
     */
    public function createArticle(string $title, string $summary, string $content, int $userId, int $categoryId): int
    {
        $sql = \App\Queries\AdminQueries::createArticle();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$title, $summary, $content, $userId, $categoryId]);
        $articleId = (int)($stmt->fetchColumn() ?: 0);
        $stmt->closeCursor();
        return $articleId;
    }

    /**
     * Cập nhật bài viết
     */
    public function updateArticle(int $id, string $title, string $summary, int $categoryId): bool
    {
        return $this->repository->update($id, [
            'title' => $title,
            'summary' => $summary,
            'category_id' => $categoryId
        ]);
    }

    /**
     * Cập nhật nội dung bài viết
     */
    public function updateContent(int $id, string $content): void
    {
        $sql = \App\Queries\ArticleQueries::updateContent();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$content, $id]);
    }

    /**
     * Xóa bài viết
     */
    public function deleteArticle(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Xuất bản bài viết
     */
    public function publishArticle(int $id): void
    {
        $sql = \App\Queries\AdminQueries::publishArticle();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $stmt->closeCursor();
    }

    /**
     * Tạo media cho bài viết
     */
    public function createMedia(int $articleId, string $mediaUrl, string $sizeClass, string $alignClass, ?string $caption): void
    {
        $sql = \App\Queries\ArticleQueries::createMedia();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$articleId, $mediaUrl, $sizeClass, $alignClass, $caption]);
    }

    /**
     * Xóa media
     */
    public function deleteMedia(int $articleId): void
    {
        $sql = \App\Queries\ArticleQueries::deleteMedia();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$articleId]);
    }

    /**
     * Xóa media chính xác (với media_id và media_url)
     */
    public function deleteMediaPrecise(int $articleId, int $mediaId, string $mediaUrl): bool
    {
        $sql = \App\Queries\ArticleQueries::deleteMediaPrecise();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$articleId, $mediaId, $mediaUrl]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Lấy media URL theo ID
     */
    public function getMediaUrlById(int $mediaId, int $articleId): ?string
    {
        $sql = \App\Queries\ArticleQueries::getMediaUrlByIdForArticle();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$mediaId, $articleId]);
        $url = $stmt->fetchColumn();
        return $url ? (string)$url : null;
    }

    /**
     * Xóa media theo ID
     */
    public function deleteMediaById(int $mediaId, int $articleId): void
    {
        $sql = \App\Queries\ArticleQueries::deleteMediaByIdForArticle();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$mediaId, $articleId]);
    }

    /**
     * Cập nhật tùy chọn media
     */
    public function updateMediaOptions(int $mediaId, int $articleId, string $sizeClass, string $alignClass, ?string $caption): void
    {
        $sql = \App\Queries\ArticleQueries::updateMediaOptions();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sizeClass, $alignClass, $caption, $mediaId, $articleId]);
    }

    /**
     * Xóa tất cả bài viết theo danh sách ID
     */
    public function deleteArticles(array $articleIds): void
    {
        if (empty($articleIds)) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($articleIds), '?'));
        $sql = "DELETE FROM articles WHERE article_id IN ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($articleIds);
    }
}


