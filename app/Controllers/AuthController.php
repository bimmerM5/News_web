<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

class AuthController extends Controller
{
    public function login(): void
    {
        $this->view('auth/login');
    }

    public function handleLogin(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        if ($username === '' || $password === '') {
            $this->view('auth/login', ['error' => 'Thiếu thông tin']);
            return;
        }
        $userModel = new UserModel();
        $user = $userModel->findByUsername($username);
        if (!$user) {
            $this->view('auth/login', ['error' => 'Sai tài khoản hoặc mật khẩu']);
            return;
        }
        $stored = (string)$user['password_hash'];
        $ok = false;
        if (preg_match('/^\$2y\$/', $stored) || preg_match('/^\$argon2/', $stored)) {
            $ok = password_verify($password, $stored);
        } else {
            // Legacy plaintext support
            $ok = hash_equals($stored, $password);
        }
        if (!$ok) {
            $this->view('auth/login', ['error' => 'Sai tài khoản hoặc mật khẩu']);
            return;
        }
        $_SESSION['user_id'] = (int)$user['user_id'];
        $_SESSION['username'] = $user['username'];
        header('Location: ../');
    }

    public function register(): void
    {
        $this->view('auth/register');
    }

    public function handleRegister(): void
    {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $fullname = trim($_POST['fullname'] ?? '');
        if ($username === '' || $email === '' || $password === '') {
            $this->view('auth/register', ['error' => 'Thiếu thông tin']);
            return;
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $pdo = (new UserModel())->pdo;
        try {
            $stmt = $pdo->prepare('CALL sp_register_user(?, ?, ?, ?)');
            $stmt->execute([$username, $hash, $email, $fullname]);
        } catch (\PDOException $e) {
            $this->view('auth/register', ['error' => 'Không thể đăng ký: ' . $e->getMessage()]);
            return;
        }
        header('Location: login');
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: login');
    }
}
