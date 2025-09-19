<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class AdminController extends Controller
{
    private function ensureAdmin(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . (require __DIR__ . '/../Config/config.php')['app']['base_url'] . '/auth/login');
            exit;
        }
        $pdo = Database::getConnection();
        $s = $pdo->prepare("SELECT 1
                             FROM user_roles ur
                             JOIN roles r ON r.role_id = ur.role_id
                             WHERE ur.user_id = ? AND r.role_name = 'admin' LIMIT 1");
        $s->execute([(int)$_SESSION['user_id']]);
        if (!$s->fetchColumn()) {
            http_response_code(403);
            echo 'Forbidden (admin only)';
            exit;
        }
    }

    public function listCategories(): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
        $rows = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
        $this->view('admin/categories/index', ['rows' => $rows]);
    }

    public function createCategory(): void
    {
        $this->ensureAdmin();
        $this->view('admin/categories/create');
    }

    public function storeCategory(): void
    {
        $this->ensureAdmin();
        $name = trim($_POST['category_name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($name !== '') {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("INSERT INTO categories(category_name, description) VALUES(?, ?)");
            $stmt->execute([$name, $desc]);
        }
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/categories');
    }

    public function editCategory(int $id): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
        $s = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
        $s->execute([$id]);
        $row = $s->fetch();
        $this->view('admin/categories/edit', ['row' => $row]);
    }

    public function updateCategory(int $id): void
    {
        $this->ensureAdmin();
        $name = trim($_POST['category_name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE categories SET category_name=?, description=? WHERE category_id=?");
        $stmt->execute([$name, $desc, $id]);
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/categories');
    }

    public function deleteCategory(int $id): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id=?");
        $stmt->execute([$id]);
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/categories');
    }

    public function listArticles(): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
        $rows = $pdo->query("SELECT a.article_id, a.title, a.status, a.created_at, c.category_name
                              FROM articles a LEFT JOIN categories c ON a.category_id=c.category_id
                              ORDER BY a.created_at DESC")->fetchAll();
        $this->view('admin/articles/index', ['rows' => $rows]);
    }

    public function createArticle(): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
        $cats = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
        $this->view('admin/articles/create', ['categories' => $cats]);
    }

    public function storeArticle(): void
    {
        $this->ensureAdmin();
        $title = trim($_POST['title'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $cat = (int)($_POST['category_id'] ?? 0);
        $user = (int)($_SESSION['user_id']);
        if ($title !== '') {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("CALL sp_create_article(?, ?, ?, ?, ?)");
            $stmt->execute([$title, $summary, $content, $user, $cat]);
            $articleId = (int)($stmt->fetchColumn() ?: 0);
            $stmt->closeCursor();
            if (isset($_FILES['images'])) {
                $this->handleMultiUploads($articleId);
            } elseif (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
                $url = $this->saveUpload($_FILES['image']);
                if ($url) {
                    $ins = $pdo->prepare("INSERT INTO article_media(article_id, media_url, media_type) VALUES(?, ?, 'image')");
                    $ins->execute([$articleId, $url]);
                }
            }
        }
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/articles');
    }

    public function editArticle(int $id): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
        $a = $pdo->prepare("SELECT * FROM articles WHERE article_id=?");
        $a->execute([$id]);
        $article = $a->fetch();
        $c = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
        $ac = $pdo->prepare("SELECT content FROM article_contents WHERE article_id=?");
        $ac->execute([$id]);
        $content = $ac->fetchColumn();
        $m = $pdo->prepare("SELECT media_url FROM article_media WHERE article_id=? AND media_type='image' ORDER BY media_id ASC");
        $m->execute([$id]);
        $images = $m->fetchAll();
        $this->view('admin/articles/edit', ['article' => $article, 'categories' => $c, 'content' => $content, 'images' => $images]);
    }

    public function updateArticle(int $id): void
    {
        $this->ensureAdmin();
        $title = trim($_POST['title'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $cat = (int)($_POST['category_id'] ?? 0);
        $pdo = Database::getConnection();
        $u = $pdo->prepare("UPDATE articles SET title=?, summary=?, category_id=? WHERE article_id=?");
        $u->execute([$title, $summary, $cat, $id]);
        $uc = $pdo->prepare("UPDATE article_contents SET content=? WHERE article_id=?");
        $uc->execute([$content, $id]);
        $this->handleMultiUploads($id, false);
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/articles');
    }

    public function deleteArticle(int $id): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
        $d = $pdo->prepare("DELETE FROM articles WHERE article_id=?");
        $d->execute([$id]);
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/articles');
    }

    public function publishArticle(int $id): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("CALL sp_publish_article(?)");
        $stmt->execute([$id]);
        $stmt->closeCursor();
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/articles');
    }

    private function handleMultiUploads(int $articleId, bool $clearExisting = false): void
    {
        if (!isset($_FILES['images'])) { return; }
        $files = $_FILES['images'];
        if ($clearExisting) {
            Database::getConnection()->prepare("DELETE FROM article_media WHERE article_id=? AND media_type='image'")->execute([$articleId]);
        }
        $count = is_array($files['name']) ? count($files['name']) : 0;
        for ($i=0; $i<$count; $i++) {
            if (!empty($files['tmp_name'][$i]) && is_uploaded_file($files['tmp_name'][$i])) {
                $file = [
                    'name' => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i]
                ];
                $url = $this->saveUpload($file);
                if ($url) {
                    $stmt = Database::getConnection()->prepare("INSERT INTO article_media(article_id, media_url, media_type) VALUES(?, ?, 'image')");
                    $stmt->execute([$articleId, $url]);
                }
            }
        }
    }

    private function saveUpload(array $file): ?string
    {
        $root = realpath(__DIR__ . '/../../public');
        $dir = $root . DIRECTORY_SEPARATOR . 'uploads';
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        $ext = pathinfo($file['name'] ?? '', PATHINFO_EXTENSION) ?: 'jpg';
        $name = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $dir . DIRECTORY_SEPARATOR . $name;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/' . $name;
        }
        return null;
    }
}
