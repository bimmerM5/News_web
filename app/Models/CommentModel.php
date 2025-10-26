<?php
namespace App\Models;

use App\Queries\CommentQueries;

class CommentModel extends BaseModel
{
    public function listForArticle(int $articleId): array
    {
        $sql = CommentQueries::listForArticle();
        $c = $this->pdo->prepare($sql);
        $c->execute([$articleId]);
        return $c->fetchAll();
    }

    public function create(int $articleId, int $userId, string $content): void
    {
        $sql = CommentQueries::create();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$articleId, $userId, $content]);
    }
}


