<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ProfileController extends Controller
{
    public function show(int $userId): void
    {
        $pdo = Database::getConnection();
        $u = $pdo->prepare("SELECT u.user_id, u.username, up.full_name, up.avatar_url, up.bio
                             FROM users u LEFT JOIN user_profiles up ON up.user_id = u.user_id
                             WHERE u.user_id=?");
        $u->execute([$userId]);
        $user = $u->fetch();
        if (!$user) {
            http_response_code(404);
            echo 'User not found';
            return;
        }
        $a = $pdo->prepare("SELECT article_id, title, status, created_at FROM articles WHERE user_id=? ORDER BY created_at DESC");
        $a->execute([$userId]);
        $articles = $a->fetchAll();
        $this->view('profile/show', ['user' => $user, 'articles' => $articles]);
    }
}
