<?php
session_start();
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Core/Database.php';
// Queries (used by models)
require_once __DIR__ . '/../app/Queries/ArticleQueries.php';
require_once __DIR__ . '/../app/Queries/CategoryQueries.php';
require_once __DIR__ . '/../app/Queries/CommentQueries.php';
require_once __DIR__ . '/../app/Queries/UserQueries.php';
require_once __DIR__ . '/../app/Queries/AdminQueries.php';
// Repository Pattern - OOP improvements
require_once __DIR__ . '/../app/Queries/RepositoryInterface.php';
require_once __DIR__ . '/../app/Queries/ArticleRepository.php';
require_once __DIR__ . '/../app/Queries/CategoryRepository.php';
require_once __DIR__ . '/../app/Queries/UserRepository.php';
require_once __DIR__ . '/../app/Queries/CommentRepository.php';
require_once __DIR__ . '/../app/Models/BaseModel.php';
require_once __DIR__ . '/../app/Models/ArticleModel.php';
require_once __DIR__ . '/../app/Models/CategoryModel.php';
require_once __DIR__ . '/../app/Models/CommentModel.php';
require_once __DIR__ . '/../app/Models/UserModel.php';
require_once __DIR__ . '/../app/Controllers/HomeController.php';
require_once __DIR__ . '/../app/Controllers/ArticleController.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Controllers/ApiController.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/SearchController.php';
require_once __DIR__ . '/../app/Controllers/ProfileController.php';

use App\Core\Router;

$router = new Router();

$router->get('/', [App\Controllers\HomeController::class, 'index']);
$router->get('/article/(\\d+)', [App\Controllers\ArticleController::class, 'show']);
$router->get('/category/(\\d+)', [App\Controllers\ArticleController::class, 'category']);
$router->get('/search', [App\Controllers\SearchController::class, 'index']);
$router->get('/user/(\\d+)', [App\Controllers\ProfileController::class, 'show']);

// Auth
$router->get('/auth/login', [App\Controllers\AuthController::class, 'login']);
$router->post('/auth/login', [App\Controllers\AuthController::class, 'handleLogin']);
$router->get('/auth/register', [App\Controllers\AuthController::class, 'register']);
$router->post('/auth/register', [App\Controllers\AuthController::class, 'handleRegister']);
$router->post('/auth/logout', [App\Controllers\AuthController::class, 'logout']);

$router->get('/admin/categories', [App\Controllers\AdminController::class, 'listCategories']);
$router->get('/admin/categories/create', [App\Controllers\AdminController::class, 'createCategory']);
$router->post('/admin/categories/store', [App\Controllers\AdminController::class, 'storeCategory']);
$router->get('/admin/categories/(\\d+)/edit', [App\Controllers\AdminController::class, 'editCategory']);
$router->post('/admin/categories/(\\d+)/update', [App\Controllers\AdminController::class, 'updateCategory']);
$router->post('/admin/categories/(\\d+)/delete', [App\Controllers\AdminController::class, 'deleteCategory']);

$router->get('/admin/articles', [App\Controllers\AdminController::class, 'listArticles']);
$router->get('/admin/articles/create', [App\Controllers\AdminController::class, 'createArticle']);
$router->post('/admin/articles/store', [App\Controllers\AdminController::class, 'storeArticle']);
$router->get('/admin/articles/(\\d+)/edit', [App\Controllers\AdminController::class, 'editArticle']);
$router->post('/admin/articles/(\\d+)/update', [App\Controllers\AdminController::class, 'updateArticle']);
$router->post('/admin/articles/(\\d+)/delete', [App\Controllers\AdminController::class, 'deleteArticle']);
$router->post('/admin/articles/(\\d+)/publish', [App\Controllers\AdminController::class, 'publishArticle']);

$router->get('/api/articles', [App\Controllers\ApiController::class, 'articles']);
$router->get('/api/article/(\\d+)', [App\Controllers\ApiController::class, 'article']);
$router->get('/api/comments', [App\Controllers\ApiController::class, 'comments']);
$router->post('/api/comments', [App\Controllers\ApiController::class, 'createComment']);
$router->post('/api/toggle-like', [App\Controllers\ApiController::class, 'toggleLike']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
