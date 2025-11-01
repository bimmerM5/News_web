<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\CategoryModel;
use App\Queries\UserQueries;
use App\Queries\ArticleQueries;
use App\Queries\CategoryQueries;
use App\Queries\AdminQueries;

class AdminController extends Controller
{
    private function ensureAdmin(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . (require __DIR__ . '/../Config/config.php')['app']['base_url'] . '/auth/login');
            exit;
        }
        $pdo = Database::getConnection();
        $s = $pdo->prepare(UserQueries::checkAdminRole());
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
        $rows = (new CategoryModel())->listAll();
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
            (new CategoryModel())->create($name, $desc);
        }
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/categories');
    }

    public function editCategory(int $id): void
    {
        $this->ensureAdmin();
        $row = (new CategoryModel())->find($id);
        $this->view('admin/categories/edit', ['row' => $row]);
    }

    public function updateCategory(int $id): void
    {
        $this->ensureAdmin();
        $name = trim($_POST['category_name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        (new CategoryModel())->update($id, $name, $desc);
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/categories');
    }

    public function deleteCategory(int $id): void
    {
        $this->ensureAdmin();
        (new CategoryModel())->delete($id);
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/categories');
    }

    public function listArticles(): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
        $rows = $pdo->query(ArticleQueries::getAllArticlesForAdmin())->fetchAll();
        $this->view('admin/articles/index', ['rows' => $rows]);
    }

    public function createArticle(): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
        $cats = $pdo->query(CategoryQueries::listAll())->fetchAll();
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
            $stmt = $pdo->prepare(AdminQueries::createArticle());
            $stmt->execute([$title, $summary, $content, $user, $cat]);
            $articleId = (int)($stmt->fetchColumn() ?: 0);
            $stmt->closeCursor();
            if (isset($_FILES['images'])) {
                $this->handleMultiUploads($articleId);
            } elseif (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
                $url = $this->saveUpload($_FILES['image']);
                if ($url) {
                    $ins = $pdo->prepare(ArticleQueries::createMedia());
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
        $a = $pdo->prepare(ArticleQueries::getById());
        $a->execute([$id]);
        $article = $a->fetch();
        $c = $pdo->query(CategoryQueries::listAll())->fetchAll();
        $ac = $pdo->prepare(ArticleQueries::getContent());
        $ac->execute([$id]);
        $content = $ac->fetchColumn();
        $m = $pdo->prepare(ArticleQueries::getMedia());
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
        $u = $pdo->prepare(ArticleQueries::updateArticle());
        $u->execute([$title, $summary, $cat, $id]);
        $uc = $pdo->prepare(ArticleQueries::updateContent());
        $uc->execute([$content, $id]);
        $this->handleMultiUploads($id, false);
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/articles');
    }

    public function deleteArticle(int $id): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
        $d = $pdo->prepare(ArticleQueries::deleteArticle());
        $d->execute([$id]);
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/articles');
    }

    public function publishArticle(int $id): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(AdminQueries::publishArticle());
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
            Database::getConnection()->prepare(ArticleQueries::deleteMedia())->execute([$articleId]);
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
                    $stmt = Database::getConnection()->prepare(ArticleQueries::createMedia());
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
