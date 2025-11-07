<?php
namespace App\Queries;

use App\Core\Database;

/**
 * Comment Repository - Tuân thủ đầy đủ OOP principles
 */
class CommentRepository implements CommentRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function find(int $id): ?array
    {
        $sql = CommentQueries::findById();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM comments ORDER BY created_at DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = CommentQueries::createDirect();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['article_id'] ?? 0,
            $data['user_id'] ?? 0,
            $data['content'] ?? ''
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = CommentQueries::update();
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['content'] ?? '',
            $id,
            $data['user_id'] ?? 0
        ]);
    }

    public function delete(int $id): bool
    {
        // Note: Comment delete requires user_id, so this is a simplified version
        // Full implementation should be in Model layer
        $sql = "DELETE FROM comments WHERE comment_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function listForArticle(int $articleId): array
    {
        $sql = CommentQueries::listForArticle();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$articleId]);
        return $stmt->fetchAll();
    }

    public function createComment(int $articleId, int $userId, string $content): int
    {
        $sql = CommentQueries::create();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$articleId, $userId, $content]);
        return (int)$this->pdo->lastInsertId();
    }

    public function countByArticle(int $articleId): int
    {
        $sql = CommentQueries::countByArticle();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$articleId]);
        return (int)$stmt->fetchColumn();
    }

    public function getUserComments(int $userId): array
    {
        $sql = CommentQueries::getUserComments();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}

