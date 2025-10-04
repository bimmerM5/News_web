<?php
namespace App\Models;

class CommentModel extends BaseModel
{
    public function listForArticle(int $articleId): array
    {
        $c = $this->pdo->prepare("SELECT c.comment_id, c.content, c.created_at, u.username
                             FROM comments c
                             JOIN users u ON u.user_id = c.user_id
                             WHERE c.article_id = ?
                             ORDER BY c.created_at ASC");
        $c->execute([$articleId]);
        return $c->fetchAll();
    }

    public function create(int $articleId, int $userId, string $content): void
    {
        $stmt = $this->pdo->prepare("CALL sp_add_comment(?, ?, ?)");
        $stmt->execute([$articleId, $userId, $content]);
    }
}


