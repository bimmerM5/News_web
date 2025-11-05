<?php
/** @var string $baseUrl */
$cfg = require __DIR__ . '/../../Config/config.php';
$baseUrl = $baseUrl ?? ($cfg['app']['base_url'] ?? '');

$themeCookie = $_COOKIE['newsweb-theme'] ?? null;
$themeAttr = ($themeCookie === 'light') ? 'light' : 'dark';
?>
<!doctype html>
<html lang="vi" data-theme="<?= htmlspecialchars($themeAttr) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>News Web</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/style.css?v=orig">
  <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/theme.css?v=2">
</head>
<body>

<nav class="navbar navbar-expand-lg px-3 py-2">
  <div class="container-fluid">
    <a class="navbar-brand fw-semibold" href="<?= htmlspecialchars($baseUrl) ?>/">News Web</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($baseUrl) ?>/">Trang chủ</a></li>
        <!-- BỎ LINK TÌM KIẾM
        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($baseUrl) ?>/search">Tìm kiếm</a></li>
        -->
        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($baseUrl) ?>/admin/articles">Quản lý bài viết</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($baseUrl) ?>/admin/categories">Danh mục</a></li>
      </ul>

      <div class="d-flex align-items-center gap-2">
        <a href="<?= htmlspecialchars($baseUrl) ?>/auth/login" class="btn btn-outline-primary btn-sm">Đăng nhập</a>
        <a href="<?= htmlspecialchars($baseUrl) ?>/auth/register" class="btn btn-primary btn-sm">Đăng ký</a>

        <button id="theme-toggle" class="btn btn-sm btn-outline-secondary" type="button">🌙 Dark</button>
      </div>
    </div>
  </div>
</nav>

<div class="container my-4">
