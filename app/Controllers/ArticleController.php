<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ArticleModel;
use App\Models\CategoryModel;
use App\Models\CommentModel;

class ArticleController extends Controller
{
    public function show(int $id): void
    {
        $articleModel = new ArticleModel();
        $details = $articleModel->getByIdWithDetails($id);
        if (!$details) {
            http_response_code(404);
            echo 'Article not found';
            return;
        }
<<<<<<< HEAD
        
        // Ghi lại lượt xem
        $userId = $_SESSION['user_id'] ?? null;
        $articleModel->addView($id, $userId);
        
=======
>>>>>>> d782790 (light and dark mode update)
        $comments = (new CommentModel())->listForArticle($id);
        $this->view('article/show', [
            'article' => $details['article'],
            'comments' => $comments,
            'articleContent' => $details['content'],
            'images' => $details['images']
        ]);
    }

    public function category(int $id): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = 9;

        // Lấy thông tin danh mục
        $categoryModel = new CategoryModel();
        $category = $categoryModel->find($id);

        // Lấy danh sách bài viết và tổng số lượng
        [$articles, $total] = (new ArticleModel())->getByCategory($id, $page, $per);
        $pages = (int)ceil($total / $per);

        // Hiển thị giao diện
        $this->view('article/category', [
            'category' => $category,
            'articles' => $articles,
            'page' => $page,
            'pages' => $pages
        ]);
    }
}
