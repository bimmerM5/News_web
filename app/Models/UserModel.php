<?php
namespace App\Models;

class UserModel extends BaseModel
{
    public function findByUsername(string $username): ?array
    {
        $s = $this->pdo->prepare('SELECT user_id, username, password_hash FROM users WHERE username = ?');
        $s->execute([$username]);
        $row = $s->fetch();
        return $row ?: null;
    }

    public function getProfile(int $userId): ?array
    {
        $u = $this->pdo->prepare("SELECT u.user_id, u.username, up.full_name, up.avatar_url, up.bio
                             FROM users u LEFT JOIN user_profiles up ON up.user_id = u.user_id
                             WHERE u.user_id=?");
        $u->execute([$userId]);
        $row = $u->fetch();
        return $row ?: null;
    }

    public function getUserArticles(int $userId): array
    {
        $a = $this->pdo->prepare("SELECT article_id, title, status, created_at FROM articles WHERE user_id=? ORDER BY created_at DESC");
        $a->execute([$userId]);
        return $a->fetchAll();
    }
}


