<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HomeController extends Controller
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = 9;
        $offset = ($page - 1) * $per;
        $catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

        $where = "WHERE a.status = 'published'";
        $params = [];
        if ($catId > 0) {
            $where .= " AND a.category_id = :cat";
            $params[':cat'] = $catId;
        }

        $sql = "SELECT a.article_id, a.title, a.summary, a.created_at, c.category_name
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.category_id
                $where
                ORDER BY a.created_at DESC
                LIMIT :per OFFSET :off";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v, \PDO::PARAM_INT); }
        $stmt->bindValue(':per', $per, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $articles = $stmt->fetchAll();

        $cntSql = "SELECT COUNT(*) FROM articles a $where";
        $cnt = $pdo->prepare($cntSql);
        foreach ($params as $k => $v) { $cnt->bindValue($k, $v, \PDO::PARAM_INT); }
        $cnt->execute();
        $total = (int)$cnt->fetchColumn();
        $pages = (int)ceil($total / $per);

        $categories = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name")->fetchAll();

        $this->view('home/index', [
            'articles' => $articles,
            'page' => $page,
            'pages' => $pages,
            'categories' => $categories,
            'selectedCat' => $catId,
        ]);
    }

    public function categories(): void
    {
        $pdo = Database::getConnection();
        $rows = $pdo->query("SELECT c.category_id, c.category_name, c.description, COUNT(a.article_id) AS total
                             FROM categories c
                             LEFT JOIN articles a ON a.category_id = c.category_id AND a.status='published'
                             GROUP BY c.category_id, c.category_name, c.description
                             ORDER BY c.category_name")->fetchAll();
        $this->view('home/categories', ['rows' => $rows]);
    }
}
