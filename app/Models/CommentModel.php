<?php
namespace App\Models;

use App\Queries\CommentRepositoryInterface;
use App\Queries\CommentRepository;

/**
 * Comment Model - Cải thiện với Repository Pattern
 */
class CommentModel extends BaseModel
{
    private CommentRepositoryInterface $repository;

    public function __construct(?\PDO $pdo = null, ?CommentRepositoryInterface $repository = null)
    {
        parent::__construct($pdo);
        $this->repository = $repository ?? new CommentRepository($this->pdo);
    }

    public function listForArticle(int $articleId): array
    {
        return $this->repository->listForArticle($articleId);
    }

    public function create(int $articleId, int $userId, string $content): void
    {
        $this->repository->createComment($articleId, $userId, $content);
    }

    /**
     * Đếm số lượng comment của bài viết
     */
    public function countByArticle(int $articleId): int
    {
        return $this->repository->countByArticle($articleId);
    }

    /**
     * Lấy comment của user
     */
    public function getUserComments(int $userId): array
    {
        return $this->repository->getUserComments($userId);
    }
}


