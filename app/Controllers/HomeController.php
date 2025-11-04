<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ArticleModel;
use App\Models\CategoryModel;

class HomeController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = 9;
        $catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

        // Lấy danh sách bài viết (nếu có bộ lọc theo category)
        $articleModel = new ArticleModel();
        [$articles, $total] = $articleModel->getPublishedArticles(
            $page,
            $per,
            $catId > 0 ? $catId : null
        );

        $pages = (int)ceil($total / $per);

        // Lấy danh sách danh mục để hiển thị trong menu
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->listAll();

        // Gửi dữ liệu sang view
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
        $categoryModel = new CategoryModel();
        $rows = $categoryModel->listWithTotals();

        $this->view('home/categories', [
            'rows' => $rows
        ]);
    }
}
