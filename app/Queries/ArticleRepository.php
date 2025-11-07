<?php
namespace App\Queries;

use App\Core\Database;

/**
 * Article Repository - Tuân thủ đầy đủ OOP principles
 * 
 * - Dependency Injection: Nhận PDO qua constructor
 * - Interface Segregation: Implement ArticleRepositoryInterface
 * - Single Responsibility: Chỉ xử lý data access cho Article
 * - Open/Closed: Có thể extend mà không modify
 */
class ArticleRepository implements ArticleRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function find(int $id): ?array
    {
        $sql = ArticleQueries::getById();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findAll(): array
    {
        $sql = ArticleQueries::getAllArticlesForAdmin();
        return $this->pdo->query($sql)->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO articles (title, summary, user_id, category_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['title'] ?? '',
            $data['summary'] ?? '',
            $data['user_id'] ?? 0,
            $data['category_id'] ?? null
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = ArticleQueries::updateArticle();
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['title'] ?? '',
            $data['summary'] ?? '',
            $data['category_id'] ?? null,
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = ArticleQueries::deleteArticle();
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getPublishedArticles(int $page, int $perPage, ?int $categoryId = null): array
    {
        $offset = ($page - 1) * $perPage;
        
        if ($categoryId) {
            $sql = ArticleQueries::getPublishedArticlesByCategory();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':cid', $categoryId, \PDO::PARAM_INT);
            $stmt->bindValue(':per', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        
        $sql = ArticleQueries::getPublishedArticles();
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':per', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByIdWithDetails(int $id): ?array
    {
        $sql = ArticleQueries::getByIdWithDetails();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        
        if (!$article) {
            return null;
        }

        $content = $this->getContent($id);
        $images = $this->getMedia($id);

        return [
            'article' => $article,
            'content' => $content,
            'images' => $images
        ];
    }

    public function getContent(int $id): string
    {
        $sql = ArticleQueries::getContent();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return (string)$stmt->fetchColumn();
    }

    public function getMedia(int $id): array
    {
        $sql = ArticleQueries::getMedia();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function countPublishedArticles(?int $categoryId = null): int
    {
        if ($categoryId) {
            $sql = ArticleQueries::countPublishedArticlesByCategory();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':cid', $categoryId, \PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $sql = ArticleQueries::countPublishedArticles();
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        }
        return (int)$stmt->fetchColumn();
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
        return $stmt->fetchAll();
    }

    public function getUserArticles(int $userId): array
    {
        $sql = ArticleQueries::getUserArticles();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
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
}

