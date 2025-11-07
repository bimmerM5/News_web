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

        try {
            // Đăng ký user mới
            $userModel->register($username, $hash, $email, null);
        } catch (\PDOException $e) {
            // Xử lý trùng username/email (SQLSTATE 23000, driver code 1062)
            $errInfo = $e->errorInfo ?? [];
            $driverCode = $errInfo[1] ?? null; // 1062 for duplicate
            $msg = (string)($errInfo[2] ?? $e->getMessage());
            $errors = [];
            if ($driverCode === 1062 || stripos($msg, 'duplicate') !== false) {
                if (stripos($msg, "for key 'username'") !== false || stripos($msg, 'username') !== false) {
                    $errors['username'] = 'Tài khoản đã tồn tại';
                }
                if (stripos($msg, "for key 'email'") !== false || stripos($msg, 'email') !== false) {
                    $errors['email'] = 'Email đã được sử dụng';
                }
                if (empty($errors)) {
                    // fallback chung nếu không xác định được cột
                    $errors['username'] = 'Tài khoản đã tồn tại';
                }
                $this->view('auth/register', [
                    'errors' => $errors,
                    'old' => ['username' => $username, 'email' => $email],
                ]);
                return;
            }
            $this->view('auth/register', [
                'error' => 'Không thể đăng ký. Vui lòng thử lại sau.',
                'old' => ['username' => $username, 'email' => $email],
            ]);
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
