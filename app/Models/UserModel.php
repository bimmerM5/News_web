<?php
namespace App\Models;

use App\Queries\UserQueries;

class UserModel extends BaseModel
{
    public function findByUsername(string $username): ?array
    {
        $sql = UserQueries::findByUsername();
        $s = $this->pdo->prepare($sql);
        $s->execute([$username]);
        $row = $s->fetch();
        return $row ?: null;
    }

    public function getProfile(int $userId): ?array
    {
        $sql = UserQueries::getProfile();
        $u = $this->pdo->prepare($sql);
        $u->execute([$userId]);
        $row = $u->fetch();
        return $row ?: null;
    }

    public function getUserArticles(int $userId): array
    {
        $sql = UserQueries::getUserArticles();
        $a = $this->pdo->prepare($sql);
        $a->execute([$userId]);
        return $a->fetchAll();
    }
}


