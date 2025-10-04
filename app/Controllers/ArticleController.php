<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ArticleController extends Controller
{
    public function show(int $id): void
    {
        $pdo = Database::getConnection();
        $a = $pdo->prepare("SELECT a.*, c.category_name, u.username
                             FROM articles a
                             LEFT JOIN categories c ON a.category_id = c.category_id
                             LEFT JOIN users u ON a.user_id = u.user_id
                             WHERE a.article_id = ?");
        $a->execute([$id]);
        $article = $a->fetch();
        if (!$article) {
            http_response_code(404);
            echo 'Article not found';
            return;
        }
        $contentStmt = $pdo->prepare("SELECT content FROM article_contents WHERE article_id = ?");
        $contentStmt->execute([$id]);
        $content = (string)$contentStmt->fetchColumn();
        $media = $pdo->prepare("SELECT media_url FROM article_media WHERE article_id=? AND media_type='image' ORDER BY media_id ASC");
        $media->execute([$id]);
        $images = $media->fetchAll();

        $c = $pdo->prepare("SELECT c.comment_id, c.content, c.created_at, u.username
                             FROM comments c
                             JOIN users u ON u.user_id = c.user_id
                             WHERE c.article_id = ?
                             ORDER BY c.created_at ASC");
        $c->execute([$id]);
        $comments = $c->fetchAll();
        $this->view('article/show', ['article' => $article, 'comments' => $comments, 'articleContent' => $content, 'images' => $images]);
    }

    public function category(int $id): void
    {
        $pdo = Database::getConnection();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = 9;
        $offset = ($page - 1) * $per;
        $cat = $pdo->prepare('SELECT * FROM categories WHERE category_id=:cid');
        $cat->bindValue(':cid', $id, \PDO::PARAM_INT);
        $cat->execute();
        $category = $cat->fetch();

    $stmt = $pdo->prepare("SELECT a.article_id, a.title, a.summary, a.created_at,
                   (SELECT am.media_url FROM article_media am WHERE am.article_id = a.article_id AND am.media_type='image' ORDER BY am.media_id ASC LIMIT 1) AS thumb
                   FROM articles a
                   WHERE a.status='published' AND a.category_id=:cid
                   ORDER BY a.created_at DESC
                   LIMIT :per OFFSET :off");
        $stmt->bindValue(':cid', $id, \PDO::PARAM_INT);
        $stmt->bindValue(':per', $per, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $articles = $stmt->fetchAll();

        $cnt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE status='published' AND category_id=:cid");
        $cnt->bindValue(':cid', $id, \PDO::PARAM_INT);
        $cnt->execute();
        $total = (int)$cnt->fetchColumn();
        $pages = (int)ceil($total / $per);

        $this->view('article/category', ['category' => $category, 'articles' => $articles, 'page' => $page, 'pages' => $pages]);
    }
}
