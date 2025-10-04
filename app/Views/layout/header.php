<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>News Web</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($baseUrl) ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rel = $path;
$base = $baseUrl ?? '';
if ($base && str_starts_with($path, $base)) {
    $rel = substr($path, strlen($base));
    if ($rel === false || $rel === '') { $rel = '/'; }
}
$active = function(array $prefixes) use ($rel) {
    foreach ($prefixes as $p) {
        if ($p === '/' && $rel === '/') return ' active';
        if ($p !== '/' && strpos($rel, $p) === 0) return ' active';
    }
    return '';
};
?>
<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?= htmlspecialchars($baseUrl) ?>/">News Web</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="topnav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link<?= $active(['/search']) ?>" href="<?= htmlspecialchars($baseUrl) ?>/search">Search</a></li>
                <li class="nav-item"><a class="nav-link<?= $active(['/admin/articles']) ?>" href="<?= htmlspecialchars($baseUrl) ?>/admin/articles">Admin Articles</a></li>
                <li class="nav-item"><a class="nav-link<?= $active(['/admin/categories']) ?>" href="<?= htmlspecialchars($baseUrl) ?>/admin/categories">Admin Categories</a></li>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <li class="nav-item text-white ms-lg-3">Xin chào, <?= htmlspecialchars($_SESSION['username'] ?? 'user') ?></li>
                    <li class="nav-item ms-lg-2">
                        <form class="d-inline" method="post" action="<?= htmlspecialchars($baseUrl) ?>/auth/logout">
                            <button class="btn btn-sm btn-outline-light">Đăng xuất</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link<?= $active(['/auth/login']) ?>" href="<?= htmlspecialchars($baseUrl) ?>/auth/login">Đăng nhập</a></li>
                    <li class="nav-item"><a class="nav-link<?= $active(['/auth/register']) ?>" href="<?= htmlspecialchars($baseUrl) ?>/auth/register">Đăng ký</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
