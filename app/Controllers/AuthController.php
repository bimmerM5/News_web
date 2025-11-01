<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\UserModel;
use App\Queries\AdminQueries;

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

        // Kiểm tra password hash
        if (preg_match('/^\$2y\$/', $stored) || preg_match('/^\$argon2/', $stored)) {
            $ok = password_verify($password, $stored);
        } else {
            // Hỗ trợ dạng mật khẩu cũ (plaintext)
            $ok = hash_equals($stored, $password);
        }

        if (!$ok) {
            $this->view('auth/login', ['error' => 'Sai tài khoản hoặc mật khẩu']);
            return;
        }

        // Lưu session
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

        if ($username === '' || $email === '' || $password === '') {
            $this->view('auth/register', ['error' => 'Thiếu thông tin']);
            return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $userModel = new UserModel();

        // Lấy PDO connection từ Database 
        $pdo = Database::getConnection();

        try {
            // Gọi stored procedure để đăng ký (không có full_name)
            $stmt = $pdo->prepare(AdminQueries::registerUser());
            $stmt->execute([$username, $hash, $email, null]);
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
