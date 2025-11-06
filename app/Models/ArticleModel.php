<?php
namespace App\Models;

use App\Queries\ArticleQueries;

class ArticleModel extends BaseModel
{
    public function getPublishedArticles(int $page, int $perPage, ?int $categoryId = null): array
    {
        $offset = ($page - 1) * $perPage;
        
        if (!empty($categoryId)) {
            $sql = ArticleQueries::getPublishedArticlesByCategory();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':cid', $categoryId, \PDO::PARAM_INT);
            $stmt->bindValue(':per', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $cntSql = ArticleQueries::countPublishedArticlesByCategory();
            $cnt = $this->pdo->prepare($cntSql);
            $cnt->bindValue(':cid', $categoryId, \PDO::PARAM_INT);
            $cnt->execute();
            $total = (int)$cnt->fetchColumn();
        } else {
            $sql = ArticleQueries::getPublishedArticles();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':per', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $cntSql = ArticleQueries::countPublishedArticles();
            $cnt = $this->pdo->prepare($cntSql);
            $cnt->execute();
            $total = (int)$cnt->fetchColumn();
        }
        
        return [$rows, $total];
    }

    public function incrementViews(int $id): bool
    {
        $sql = ArticleQueries::incrementViews();
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function addView(int $articleId, ?int $userId = null): bool
    {
        $sql = ArticleQueries::addView();
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$articleId, $userId]);
    }

    public function getByIdWithDetails(int $id): ?array
    {
        $sql = ArticleQueries::getByIdWithDetails();
        $a = $this->pdo->prepare($sql);
        $a->execute([$id]);
        $article = $a->fetch();
        if (!$article) { return null; }

        $contentSql = ArticleQueries::getContent();
        $contentStmt = $this->pdo->prepare($contentSql);
        $contentStmt->execute([$id]);
        $content = (string)$contentStmt->fetchColumn();

        $mediaSql = ArticleQueries::getMedia();
        $media = $this->pdo->prepare($mediaSql);
        $media->execute([$id]);
        $images = $media->fetchAll();

        return ['article' => $article, 'content' => $content, 'images' => $images];
    }

    public function getByCategory(int $categoryId, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = ArticleQueries::getPublishedArticlesByCategory();
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':cid', $categoryId, \PDO::PARAM_INT);
        $stmt->bindValue(':per', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $articles = $stmt->fetchAll();

        $cntSql = ArticleQueries::countPublishedArticlesByCategory();
        $cnt = $this->pdo->prepare($cntSql);
        $cnt->bindValue(':cid', $categoryId, \PDO::PARAM_INT);
        $cnt->execute();
        $total = (int)$cnt->fetchColumn();

        return [$articles, $total];
    }

    public function searchArticles(string $keyword, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = ArticleQueries::searchArticles();
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':kw1', '%' . $keyword . '%');
        $stmt->bindValue(':kw2', '%' . $keyword . '%');
        $stmt->bindValue(':per', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $articles = $stmt->fetchAll();

        $cntSql = ArticleQueries::countSearchResults();
        $cnt = $this->pdo->prepare($cntSql);
        $cnt->bindValue(':kw1', '%' . $keyword . '%');
        $cnt->bindValue(':kw2', '%' . $keyword . '%');
        $cnt->execute();
        $total = (int)$cnt->fetchColumn();

        return [$articles, $total];
    }
}


