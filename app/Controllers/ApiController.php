<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ApiController extends Controller
{
    public function articles(): void
    {
        $pdo = Database::getConnection();
        $q = $pdo->query("SELECT article_id, title, summary, created_at FROM articles WHERE status='published' ORDER BY created_at DESC LIMIT 20");
        $this->json(['data' => $q->fetchAll()]);
    }

    public function article(int $id): void
    {
        $pdo = Database::getConnection();
        $s = $pdo->prepare("SELECT article_id, title, summary, created_at FROM articles WHERE article_id = ?");
        $s->execute([$id]);
        $this->json(['data' => $s->fetch()]);
    }

    public function comments(): void
    {
        $articleId = (int)($_GET['article_id'] ?? 0);
        $pdo = Database::getConnection();
        $s = $pdo->prepare("SELECT c.comment_id, c.content, c.created_at, u.username
                             FROM comments c JOIN users u ON u.user_id = c.user_id
                             WHERE c.article_id = ? ORDER BY c.created_at ASC");
        $s->execute([$articleId]);
        $this->json(['data' => $s->fetchAll()]);
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
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("CALL sp_add_comment(?, ?, ?)");
        $stmt->execute([$articleId, (int)$_SESSION['user_id'], $content]);
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
