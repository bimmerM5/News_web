<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ArticleModel;
use App\Models\CategoryModel;

class HomeController extends Controller
{
    public function index(): void
    {
        // input
        $q     = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $per   = 9;
        $catId = (int)($_GET['cat'] ?? 0);
        $catId = $catId > 0 ? $catId : null;

        // data
        $articleModel = new ArticleModel();
        if ($q !== '') {
            // tìm theo tiêu đề/nội dung, vẫn hỗ trợ lọc danh mục và phân trang
            [$articles, $total] = $articleModel->searchPublished($q, $page, $per, $catId);
        } else {
            [$articles, $total] = $articleModel->getPublishedArticles($page, $per, $catId);
        }
        $pages = max(1, (int)ceil($total / $per));

        $categoryModel = new CategoryModel();
        $categories    = $categoryModel->listAll();

        // view
        $this->view('home/index', [
            'articles'    => $articles,
            'page'        => $page,
            'pages'       => $pages,
            'total'       => $total,
            'categories'  => $categories,
            'selectedCat' => $catId,
            'q'           => $q,
        ]);
    }

    public function categories(): void
    {
        $categoryModel = new CategoryModel();
        $rows = $categoryModel->listWithTotals();

        $this->view('home/categories', [
            'rows' => $rows
        ]);
    }
}
