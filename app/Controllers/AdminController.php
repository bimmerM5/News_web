<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\CategoryModel;
use App\Models\ArticleModel;
use App\Models\UserModel;

class AdminController extends Controller
{
    private function ensureAdmin(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . (require __DIR__ . '/../Config/config.php')['app']['base_url'] . '/auth/login');
            exit;
        }
        $userModel = new UserModel();
        if (!$userModel->isAdmin((int)$_SESSION['user_id'])) {
            http_response_code(403);
            echo 'Forbidden (admin only)';
            exit;
        }
    }

    public function listCategories(): void
    {
        $this->ensureAdmin();
        $categoryModel = new CategoryModel();
        $rows = $categoryModel->listAllById();
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
        $categoryModel = new CategoryModel();
        $articleModel = new ArticleModel();
        
        // Lấy danh sách ID bài viết thuộc danh mục
        $articleIds = $categoryModel->getArticleIdsByCategory($id);
        
        // Xóa media files và media records của các bài viết
        foreach ($articleIds as $articleId) {
            // Lấy tất cả media của bài viết
            $mediaList = $articleModel->getMedia($articleId);
            
            // Xóa file vật lý
            foreach ($mediaList as $media) {
                if (!empty($media['media_url'])) {
                    $this->removePhysicalFile($media['media_url']);
                }
            }
            
            // Xóa media records
            $articleModel->deleteMedia($articleId);
        }
        
        // Xóa tất cả bài viết thuộc danh mục
        if (!empty($articleIds)) {
            $articleModel->deleteArticles($articleIds);
        }
        
        // Cuối cùng mới xóa danh mục
        $categoryModel->delete($id);
        
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/categories');
    }

    public function listArticles(): void
    {
        $this->ensureAdmin();
        $articleModel = new ArticleModel();
        $rows = $articleModel->getAllForAdmin();
        $this->view('admin/articles/index', ['rows' => $rows]);
    }

    public function createArticle(): void
    {
        $this->ensureAdmin();
        $categoryModel = new CategoryModel();
        $cats = $categoryModel->listAll();
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
            $articleModel = new ArticleModel();
            $articleId = $articleModel->createArticle($title, $summary, $content, $user, $cat);
            
            if (isset($_FILES['images'])) {
                $this->handleMultiUploads($articleId);
            } elseif (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
                $url = $this->saveUpload($_FILES['image']);
                if ($url) {
                    $size = ($_POST['image_size'][0] ?? 'img-medium');
                    $align = ($_POST['image_align'][0] ?? 'img-center');
                    $caption = ($_POST['image_caption'][0] ?? null);
                    $articleModel->createMedia($articleId, $url, $size, $align, $caption);
                }
            }
        }
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/articles');
    }

    public function editArticle(int $id): void
    {
        $this->ensureAdmin();
        $articleModel = new ArticleModel();
        $article = $articleModel->findById($id);
        if (!$article) {
            http_response_code(404);
            echo 'Article not found';
            return;
        }
        
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->listAll();
        $content = $articleModel->getContent($id);
        $images = $articleModel->getMedia($id);
        
        $this->view('admin/articles/edit', [
            'article' => $article,
            'categories' => $categories,
            'content' => $content,
            'images' => $images
        ]);
    }

    public function updateArticle(int $id): void
    {
        $this->ensureAdmin();
        $title = trim($_POST['title'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $cat = (int)($_POST['category_id'] ?? 0);
        
        $articleModel = new ArticleModel();
        $articleModel->updateArticle($id, $title, $summary, $cat);
        $articleModel->updateContent($id, $content);

        // Delete selected images first
        $delKeys = $_POST['delete_media_key'] ?? [];
        if (!empty($delKeys)) {
            foreach ($delKeys as $key) {
                // Key format: "<media_id>|<media_url>"
                $parts = explode('|', (string)$key, 2);
                $mid = isset($parts[0]) ? (int)$parts[0] : -1;
                $url = $parts[1] ?? '';
                if ($mid < 0 || $url === '') continue;
                
                if ($articleModel->deleteMediaPrecise($id, $mid, $url)) {
                    $this->removePhysicalFile((string)$url);
                }
            }
        } else {
            // Backward compatibility: old form sent only ids
            $delIds = $_POST['delete_media_id'] ?? [];
            if (!empty($delIds)) {
                foreach ($delIds as $mid) {
                    $mid = (int)$mid;
                    $url = $articleModel->getMediaUrlById($mid, $id);
                    $articleModel->deleteMediaById($mid, $id);
                    if ($url) {
                        $this->removePhysicalFile($url);
                    }
                }
            }
        }

        // Update existing media display options
        $mediaIdsPosted = $_POST['existing_media_id'] ?? [];
        $sizesIdx       = $_POST['existing_size'] ?? [];
        $alignsIdx      = $_POST['existing_align'] ?? [];
        $captionsIdx    = $_POST['existing_caption'] ?? [];

        // Load current values
        $current = [];
        $images = $articleModel->getMedia($id);
        foreach ($images as $row) {
            $current[(int)$row['media_id']] = $row;
        }

        // Build a set of media IDs to update
        $idsToUpdate = [];
        foreach ((array)$mediaIdsPosted as $val) { $v = (int)$val; if ($v >= 0) $idsToUpdate[$v] = true; }
        foreach (array_keys((array)$sizesIdx) as $k) { $v = (int)$k; if ($v >= 0) $idsToUpdate[$v] = true; }
        foreach (array_keys((array)$alignsIdx) as $k) { $v = (int)$k; if ($v >= 0) $idsToUpdate[$v] = true; }
        foreach (array_keys((array)$captionsIdx) as $k) { $v = (int)$k; if ($v >= 0) $idsToUpdate[$v] = true; }

        if (!empty($idsToUpdate)) {
            foreach (array_keys($idsToUpdate) as $mid) {
                if (!isset($current[$mid])) { continue; }
                
                $size    = $sizesIdx[$mid]    ?? ($sizesIdx[array_search($mid, (array)$mediaIdsPosted, true) ?? -1]    ?? ($current[$mid]['size_class']  ?? 'img-medium'));
                $align   = $alignsIdx[$mid]   ?? ($alignsIdx[array_search($mid, (array)$mediaIdsPosted, true) ?? -1]   ?? ($current[$mid]['align_class'] ?? 'img-center'));
                $caption = $captionsIdx[$mid] ?? ($captionsIdx[array_search($mid, (array)$mediaIdsPosted, true) ?? -1] ?? ($current[$mid]['caption']     ?? null));

                $size = in_array($size, ['img-small','img-medium','img-large'], true) ? $size : ($current[$mid]['size_class'] ?? 'img-medium');
                $align = in_array($align, ['img-left','img-center','img-right'], true) ? $align : ($current[$mid]['align_class'] ?? 'img-center');
                $caption = ($caption === null) ? null : (string)$caption;

                $articleModel->updateMediaOptions($mid, $id, $size, $align, $caption);
            }
        }

        // Handle any newly added images
        $this->handleMultiUploads($id, false);
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/articles');
    }

    public function deleteArticle(int $id): void
    {
        $this->ensureAdmin();
        $articleModel = new ArticleModel();
        $articleModel->deleteArticle($id);
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/articles');
    }

    public function publishArticle(int $id): void
    {
        $this->ensureAdmin();
        $articleModel = new ArticleModel();
        $articleModel->publishArticle($id);
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        header('Location: ' . $base . '/admin/articles');
    }

    private function handleMultiUploads(int $articleId, bool $clearExisting = false): void
    {
        if (!isset($_FILES['images'])) { return; }
        $files = $_FILES['images'];
        $articleModel = new ArticleModel();
        
        if ($clearExisting) {
            $articleModel->deleteMedia($articleId);
        }
        
        $sizes = $_POST['image_size'] ?? [];
        $aligns = $_POST['image_align'] ?? [];
        $captions = $_POST['image_caption'] ?? [];
        $count = is_array($files['name']) ? count($files['name']) : 0;
        
        for ($i=0; $i<$count; $i++) {
            if (!empty($files['tmp_name'][$i]) && is_uploaded_file($files['tmp_name'][$i])) {
                $file = [
                    'name' => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i]
                ];
                $url = $this->saveUpload($file);
                if ($url) {
                    $size = $sizes[$i] ?? 'img-medium';
                    $align = $aligns[$i] ?? 'img-center';
                    $caption = $captions[$i] ?? null;
                    $articleModel->createMedia($articleId, $url, $size, $align, $caption);
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

    private function removePhysicalFile(string $mediaUrl): void
    {
        $root = realpath(__DIR__ . '/../../public');
        $rel = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $mediaUrl);
        $path = $root . DIRECTORY_SEPARATOR . ltrim($rel, DIRECTORY_SEPARATOR);
        if (is_file($path)) { @unlink($path); }
    }
}

