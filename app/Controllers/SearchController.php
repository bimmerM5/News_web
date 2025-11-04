<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ArticleModel;

class SearchController extends Controller
{
    public function index(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = 9;

        $articles = [];
        $pages = 1;

        if ($q !== '') {
            $articleModel = new ArticleModel();
<<<<<<< HEAD
            [$articles, $total] = $articleModel->searchArticles($q, $page, $per);
            $pages = (int)ceil($total / $per);
=======
            $pdo = $articleModel->pdo;

            $offset = ($page - 1) * $per;

            // Lấy danh sách bài viết theo từ khóa
            $stmt = $pdo->prepare("
                SELECT a.article_id, a.title, a.summary, a.created_at,
                       (SELECT am.media_url 
                        FROM article_media am 
                        WHERE am.article_id = a.article_id 
                          AND am.media_type='image' 
                        ORDER BY am.media_id ASC 
                        LIMIT 1) AS thumb
                FROM articles a
                WHERE a.status='published' 
                  AND (a.title LIKE :kw OR a.summary LIKE :kw)
                ORDER BY a.created_at DESC
                LIMIT :per OFFSET :off
            ");
            $stmt->bindValue(':kw', '%' . $q . '%');
            $stmt->bindValue(':per', $per, \PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $articles = $stmt->fetchAll();

            // Đếm tổng số bài viết phù hợp
            $cnt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM articles 
                WHERE status='published' 
                  AND (title LIKE :kw OR summary LIKE :kw)
            ");
            $cnt->bindValue(':kw', '%' . $q . '%');
            $cnt->execute();

            $pages = (int)ceil(((int)$cnt->fetchColumn()) / $per);
>>>>>>> d782790 (light and dark mode update)
        }

        $this->view('search/index', [
            'q' => $q,
            'articles' => $articles,
            'page' => $page,
            'pages' => $pages
        ]);
    }
}
