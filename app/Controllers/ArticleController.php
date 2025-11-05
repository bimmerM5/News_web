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
        $model = new ArticleModel();
        $row   = $model->getByIdWithDetails($id);

        if (!$row) {
            http_response_code(404);
            // Có thể render view 404 riêng nếu bạn có
            echo "<h2>Bài viết không tồn tại hoặc đã bị gỡ.</h2>";
            return;
        }

        // Chuẩn hóa dữ liệu để view cũ dùng được
        $this->view('article/show', [
            'article' => $row,                    // toàn bộ cột của articles + category_name
            'images'  => $row['media'] ?? [],     // danh sách ảnh/ media
            // Nếu DB không có cột content thì dùng summary làm nội dung để tránh null
            'content' => $row['content'] ?? ($row['summary'] ?? ''),
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
