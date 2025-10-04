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
        $per = 9; $offset = ($page-1)*$per;
        $articles = []; $pages = 1;
        if ($q !== '') {
            // use raw pdo through ArticleModel for now; could be a dedicated search method
            $model = new ArticleModel();
            $stmt = $model->pdo->prepare("SELECT a.article_id, a.title, a.summary, a.created_at
                                   FROM articles a
                                   WHERE a.status='published' AND (a.title LIKE :kw1 OR a.summary LIKE :kw2)
                                   ORDER BY a.created_at DESC
                                   LIMIT :per OFFSET :off");
            $stmt->bindValue(':kw1', '%' . $q . '%');
            $stmt->bindValue(':kw2', '%' . $q . '%');
            $stmt->bindValue(':per', $per, \PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $articles = $stmt->fetchAll();
            $cnt = $model->pdo->prepare("SELECT COUNT(*) FROM articles WHERE status='published' AND (title LIKE :kw1 OR summary LIKE :kw2)");
            $cnt->bindValue(':kw1', '%' . $q . '%');
            $cnt->bindValue(':kw2', '%' . $q . '%');
            $cnt->execute();
            $pages = (int)ceil(((int)$cnt->fetchColumn()) / $per);
        }
        $this->view('search/index', ['q' => $q, 'articles' => $articles, 'page' => $page, 'pages' => $pages]);
    }
}
