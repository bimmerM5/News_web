<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

class ProfileController extends Controller
{
    public function show(int $userId): void
    {
        $userModel = new UserModel();
        $user = $userModel->getProfile($userId);
        if (!$user) {
            http_response_code(404);
            echo 'User not found';
            return;
        }
        $articles = $userModel->getUserArticles($userId);
        $this->view('profile/show', ['user' => $user, 'articles' => $articles]);
    }
}
