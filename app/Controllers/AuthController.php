<?php
namespace App\Controllers;

use App\Core\Controller;
<<<<<<< HEAD
use App\Core\Database;
use App\Models\UserModel;
use App\Queries\AdminQueries;
=======
use App\Models\UserModel;
>>>>>>> d782790 (light and dark mode update)

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
<<<<<<< HEAD

=======
>>>>>>> d782790 (light and dark mode update)
        if ($username === '' || $password === '') {
            $this->view('auth/login', ['error' => 'Thiếu thông tin']);
            return;
        }
<<<<<<< HEAD

        $userModel = new UserModel();
        $user = $userModel->findByUsername($username);

=======
        $userModel = new UserModel();
        $user = $userModel->findByUsername($username);
>>>>>>> d782790 (light and dark mode update)
        if (!$user) {
            $this->view('auth/login', ['error' => 'Sai tài khoản hoặc mật khẩu']);
            return;
        }
<<<<<<< HEAD

        $stored = (string)$user['password_hash'];
        $ok = false;

        // Kiểm tra password hash
        if (preg_match('/^\$2y\$/', $stored) || preg_match('/^\$argon2/', $stored)) {
            $ok = password_verify($password, $stored);
        } else {
            // Hỗ trợ dạng mật khẩu cũ (plaintext)
            $ok = hash_equals($stored, $password);
        }

=======
        $stored = (string)$user['password_hash'];
        $ok = false;
        if (preg_match('/^\$2y\$/', $stored) || preg_match('/^\$argon2/', $stored)) {
            $ok = password_verify($password, $stored);
        } else {
            // Legacy plaintext support
            $ok = hash_equals($stored, $password);
        }
>>>>>>> d782790 (light and dark mode update)
        if (!$ok) {
            $this->view('auth/login', ['error' => 'Sai tài khoản hoặc mật khẩu']);
            return;
        }
<<<<<<< HEAD

        // Lưu session
        $_SESSION['user_id'] = (int)$user['user_id'];
        $_SESSION['username'] = $user['username'];

=======
        $_SESSION['user_id'] = (int)$user['user_id'];
        $_SESSION['username'] = $user['username'];
>>>>>>> d782790 (light and dark mode update)
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
<<<<<<< HEAD

=======
        $fullname = trim($_POST['fullname'] ?? '');
>>>>>>> d782790 (light and dark mode update)
        if ($username === '' || $email === '' || $password === '') {
            $this->view('auth/register', ['error' => 'Thiếu thông tin']);
            return;
        }
<<<<<<< HEAD

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $userModel = new UserModel();

        // Lấy PDO connection từ Database 
        $pdo = Database::getConnection();

        try {
            // Gọi stored procedure để đăng ký (không có full_name)
            $stmt = $pdo->prepare(AdminQueries::registerUser());
            $stmt->execute([$username, $hash, $email, null]);
=======
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $pdo = (new UserModel())->pdo;
        try {
            $stmt = $pdo->prepare('CALL sp_register_user(?, ?, ?, ?)');
            $stmt->execute([$username, $hash, $email, $fullname]);
>>>>>>> d782790 (light and dark mode update)
        } catch (\PDOException $e) {
            $this->view('auth/register', ['error' => 'Không thể đăng ký: ' . $e->getMessage()]);
            return;
        }
<<<<<<< HEAD

=======
>>>>>>> d782790 (light and dark mode update)
        header('Location: login');
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: login');
    }
}
