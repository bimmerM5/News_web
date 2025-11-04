<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ArticleModel;
use App\Models\CommentModel;
use App\Core\Database;

class ApiController extends Controller
{
    public function articles(): void
    {
        [$rows, $total] = (new ArticleModel())->getPublishedArticles(1, 20, null);
        $this->json(['data' => $rows]);
    }

    public function article(int $id): void
    {
        $details = (new ArticleModel())->getByIdWithDetails($id);
        $this->json(['data' => $details ? $details['article'] : null]);
    }

    public function comments(): void
    {
        $articleId = (int)($_GET['article_id'] ?? 0);
        $comments = (new CommentModel())->listForArticle($articleId);
        $this->json(['data' => $comments]);
    }

    public function createComment(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $articleId = (int)($input['article_id'] ?? 0);
        $content = trim((string)($input['content'] ?? ''));
        if ($articleId <= 0 || $content === '') {
            $this->json(['error' => 'Invalid input'], 400);
            return;
        }
        (new CommentModel())->create($articleId, (int)$_SESSION['user_id'], $content);
        $this->json(['message' => 'ok']);
    }

    public function toggleLike(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $articleId = (int)($input['article_id'] ?? 0);
        if ($articleId <= 0) {
            $this->json(['error' => 'Invalid input'], 400);
            return;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("CALL sp_toggle_like(?, ?)");
        $stmt->execute([$articleId, (int)$_SESSION['user_id']]);
        $this->json(['message' => 'ok']);
    }
}
