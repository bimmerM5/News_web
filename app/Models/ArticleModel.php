<?php
namespace App\Models;

class ArticleModel extends BaseModel
{
    public function getPublishedArticles(int $page, int $perPage, ?int $categoryId = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = "WHERE a.status = 'published'";
        $params = [];
        if (!empty($categoryId)) {
            $where .= " AND a.category_id = :cat";
            $params[':cat'] = $categoryId;
        }
        $sql = "SELECT a.article_id, a.title, a.summary, a.created_at, c.category_name
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.category_id
                $where
                ORDER BY a.created_at DESC
                LIMIT :per OFFSET :off";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v, \PDO::PARAM_INT); }
        $stmt->bindValue(':per', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $cntSql = "SELECT COUNT(*) FROM articles a $where";
        $cnt = $this->pdo->prepare($cntSql);
        foreach ($params as $k => $v) { $cnt->bindValue($k, $v, \PDO::PARAM_INT); }
        $cnt->execute();
        $total = (int)$cnt->fetchColumn();
        return [$rows, $total];
    }

    public function getByIdWithDetails(int $id): ?array
    {
        $a = $this->pdo->prepare("SELECT a.*, c.category_name, u.username
                                   FROM articles a
                                   LEFT JOIN categories c ON a.category_id = c.category_id
                                   LEFT JOIN users u ON a.user_id = u.user_id
                                   WHERE a.article_id = ?");
        $a->execute([$id]);
        $article = $a->fetch();
        if (!$article) { return null; }

        $contentStmt = $this->pdo->prepare("SELECT content FROM article_contents WHERE article_id = ?");
        $contentStmt->execute([$id]);
        $content = (string)$contentStmt->fetchColumn();

        $media = $this->pdo->prepare("SELECT media_url FROM article_media WHERE article_id=? AND media_type='image' ORDER BY media_id ASC");
        $media->execute([$id]);
        $images = $media->fetchAll();

        return ['article' => $article, 'content' => $content, 'images' => $images];
    }

    public function getByCategory(int $categoryId, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->pdo->prepare("SELECT a.article_id, a.title, a.summary, a.created_at
                                      FROM articles a
                                      WHERE a.status='published' AND a.category_id=:cid
                                      ORDER BY a.created_at DESC
                                      LIMIT :per OFFSET :off");
        $stmt->bindValue(':cid', $categoryId, \PDO::PARAM_INT);
        $stmt->bindValue(':per', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $articles = $stmt->fetchAll();

        $cnt = $this->pdo->prepare("SELECT COUNT(*) FROM articles WHERE status='published' AND category_id=:cid");
        $cnt->bindValue(':cid', $categoryId, \PDO::PARAM_INT);
        $cnt->execute();
        $total = (int)$cnt->fetchColumn();

        return [$articles, $total];
    }
}


