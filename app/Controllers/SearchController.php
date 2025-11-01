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
            [$articles, $total] = $articleModel->searchArticles($q, $page, $per);
            $pages = (int)ceil($total / $per);
        }

        $this->view('search/index', [
            'q' => $q,
            'articles' => $articles,
            'page' => $page,
            'pages' => $pages
        ]);
    }
}
