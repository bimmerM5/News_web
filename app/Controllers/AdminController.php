<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\CategoryModel;
<<<<<<< HEAD
use App\Queries\UserQueries;
use App\Queries\ArticleQueries;
use App\Queries\CategoryQueries;
use App\Queries\AdminQueries;
=======
>>>>>>> d782790 (light and dark mode update)

class AdminController extends Controller
{
    private function ensureAdmin(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . (require __DIR__ . '/../Config/config.php')['app']['base_url'] . '/auth/login');
            exit;
        }
        $pdo = Database::getConnection();
<<<<<<< HEAD
        $s = $pdo->prepare(UserQueries::checkAdminRole());
=======
        $s = $pdo->prepare("SELECT 1
                             FROM user_roles ur
                             JOIN roles r ON r.role_id = ur.role_id
                             WHERE ur.user_id = ? AND r.role_name = 'admin' LIMIT 1");
>>>>>>> d782790 (light and dark mode update)
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
<<<<<<< HEAD
        $rows = $pdo->query(ArticleQueries::getAllArticlesForAdmin())->fetchAll();
=======
        $rows = $pdo->query("SELECT a.article_id, a.title, a.status, a.created_at, c.category_name
                              FROM articles a LEFT JOIN categories c ON a.category_id=c.category_id
                              ORDER BY a.created_at DESC")->fetchAll();
>>>>>>> d782790 (light and dark mode update)
        $this->view('admin/articles/index', ['rows' => $rows]);
    }

    public function createArticle(): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
<<<<<<< HEAD
        $cats = $pdo->query(CategoryQueries::listAll())->fetchAll();
=======
        $cats = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
>>>>>>> d782790 (light and dark mode update)
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
<<<<<<< HEAD
            $stmt = $pdo->prepare(AdminQueries::createArticle());
=======
            $stmt = $pdo->prepare("CALL sp_create_article(?, ?, ?, ?, ?)");
>>>>>>> d782790 (light and dark mode update)
            $stmt->execute([$title, $summary, $content, $user, $cat]);
            $articleId = (int)($stmt->fetchColumn() ?: 0);
            $stmt->closeCursor();
            if (isset($_FILES['images'])) {
                $this->handleMultiUploads($articleId);
            } elseif (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
                $url = $this->saveUpload($_FILES['image']);
                if ($url) {
<<<<<<< HEAD
                    $size = ($_POST['image_size'][0] ?? 'img-medium');
                    $align = ($_POST['image_align'][0] ?? 'img-center');
                    $caption = ($_POST['image_caption'][0] ?? null);
                    $ins = $pdo->prepare(ArticleQueries::createMedia());
                    $ins->execute([$articleId, $url, $size, $align, $caption]);
=======
                    $ins = $pdo->prepare("INSERT INTO article_media(article_id, media_url, media_type) VALUES(?, ?, 'image')");
                    $ins->execute([$articleId, $url]);
>>>>>>> d782790 (light and dark mode update)
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
<<<<<<< HEAD
        $a = $pdo->prepare(ArticleQueries::getById());
        $a->execute([$id]);
        $article = $a->fetch();
        $c = $pdo->query(CategoryQueries::listAll())->fetchAll();
        $ac = $pdo->prepare(ArticleQueries::getContent());
        $ac->execute([$id]);
        $content = $ac->fetchColumn();
        $m = $pdo->prepare(ArticleQueries::getMedia());
=======
        $a = $pdo->prepare("SELECT * FROM articles WHERE article_id=?");
        $a->execute([$id]);
        $article = $a->fetch();
        $c = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
        $ac = $pdo->prepare("SELECT content FROM article_contents WHERE article_id=?");
        $ac->execute([$id]);
        $content = $ac->fetchColumn();
        $m = $pdo->prepare("SELECT media_url FROM article_media WHERE article_id=? AND media_type='image' ORDER BY media_id ASC");
>>>>>>> d782790 (light and dark mode update)
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
<<<<<<< HEAD
        $u = $pdo->prepare(ArticleQueries::updateArticle());
        $u->execute([$title, $summary, $cat, $id]);
        $uc = $pdo->prepare(ArticleQueries::updateContent());
        $uc->execute([$content, $id]);

        // Delete selected images first
        $delIds = $_POST['delete_media_id'] ?? [];
        if (!empty($delIds)) {
            $sel = $pdo->prepare(ArticleQueries::getMediaUrlByIdForArticle());
            $del = $pdo->prepare(ArticleQueries::deleteMediaByIdForArticle());
            foreach ($delIds as $mid) {
                $mid = (int)$mid;
                if ($mid <= 0) { continue; }
                $sel->execute([$mid, $id]);
                $url = $sel->fetchColumn();
                $del->execute([$mid, $id]);
                if ($url) { $this->removePhysicalFile((string)$url); }
            }
        }

        // Update existing media display options
        $mediaIds = $_POST['existing_media_id'] ?? [];
        $sizes    = $_POST['existing_size'] ?? [];
        $aligns   = $_POST['existing_align'] ?? [];
        $captions = $_POST['existing_caption'] ?? [];
        if (!empty($mediaIds)) {
            $um = $pdo->prepare(ArticleQueries::updateMediaOptions());
            foreach ($mediaIds as $i => $mid) {
                $mid = (int)$mid;
                if ($mid <= 0) { continue; }
                $size = $sizes[$i] ?? 'img-medium';
                $align = $aligns[$i] ?? 'img-center';
                $caption = $captions[$i] ?? null;
                $um->execute([$size, $align, $caption, $mid, $id]);
            }
        }

        // Handle any newly added images
=======
        $u = $pdo->prepare("UPDATE articles SET title=?, summary=?, category_id=? WHERE article_id=?");
        $u->execute([$title, $summary, $cat, $id]);
        $uc = $pdo->prepare("UPDATE article_contents SET content=? WHERE article_id=?");
        $uc->execute([$content, $id]);
>>>>>>> d782790 (light and dark mode update)
        $this->handleMultiUploads($id, false);
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/articles');
    }

    public function deleteArticle(int $id): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
<<<<<<< HEAD
        $d = $pdo->prepare(ArticleQueries::deleteArticle());
=======
        $d = $pdo->prepare("DELETE FROM articles WHERE article_id=?");
>>>>>>> d782790 (light and dark mode update)
        $d->execute([$id]);
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/articles');
    }

    public function publishArticle(int $id): void
    {
        $this->ensureAdmin();
        $pdo = Database::getConnection();
<<<<<<< HEAD
        $stmt = $pdo->prepare(AdminQueries::publishArticle());
=======
        $stmt = $pdo->prepare("CALL sp_publish_article(?)");
>>>>>>> d782790 (light and dark mode update)
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
<<<<<<< HEAD
            Database::getConnection()->prepare(ArticleQueries::deleteMedia())->execute([$articleId]);
        }
        $sizes = $_POST['image_size'] ?? [];
        $aligns = $_POST['image_align'] ?? [];
        $captions = $_POST['image_caption'] ?? [];
=======
            Database::getConnection()->prepare("DELETE FROM article_media WHERE article_id=? AND media_type='image'")->execute([$articleId]);
        }
>>>>>>> d782790 (light and dark mode update)
        $count = is_array($files['name']) ? count($files['name']) : 0;
        for ($i=0; $i<$count; $i++) {
            if (!empty($files['tmp_name'][$i]) && is_uploaded_file($files['tmp_name'][$i])) {
                $file = [
                    'name' => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i]
                ];
                $url = $this->saveUpload($file);
                if ($url) {
<<<<<<< HEAD
                    $size = $sizes[$i] ?? 'img-medium';
                    $align = $aligns[$i] ?? 'img-center';
                    $caption = $captions[$i] ?? null;
                    $stmt = Database::getConnection()->prepare(ArticleQueries::createMedia());
                    $stmt->execute([$articleId, $url, $size, $align, $caption]);
=======
                    $stmt = Database::getConnection()->prepare("INSERT INTO article_media(article_id, media_url, media_type) VALUES(?, ?, 'image')");
                    $stmt->execute([$articleId, $url]);
>>>>>>> d782790 (light and dark mode update)
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
<<<<<<< HEAD

    private function removePhysicalFile(string $mediaUrl): void
    {
        $root = realpath(__DIR__ . '/../../public');
        $rel = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $mediaUrl);
        $path = $root . DIRECTORY_SEPARATOR . ltrim($rel, DIRECTORY_SEPARATOR);
        if (is_file($path)) { @unlink($path); }
    }
=======
>>>>>>> d782790 (light and dark mode update)
}
