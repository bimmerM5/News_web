<?php
/** SAFETY NORMALIZATION **/
$article  = $article  ?? [];
$images   = $images   ?? [];
$comments = $comments ?? [];

// base url (đã có sẵn từ layout/header, fallback rỗng để không báo lỗi)
$baseUrl  = $baseUrl  ?? '';

// title/author/category/date
$title        = $article['title']         ?? 'Bài viết';
$author       = $article['username']      ?? '—';
$categoryName = $article['category_name'] ?? '—';
$createdAt    = $article['created_at']    ?? '';

// nội dung: ưu tiên $content do controller truyền; nếu không có thì dùng content/summary
$content = $content
    ?? ($articleContent ?? ($article['content'] ?? ($article['summary'] ?? '')));

// id bài viết (để post comment)
$articleId = (int)($article['article_id'] ?? 0);
?>

<div class="row">
  <div class="col-lg-9 mx-auto">

    <!-- Tiêu đề -->
    <h1 class="article-title display-6 mb-2">
      <?= htmlspecialchars($title) ?>
    </h1>

    <!-- Meta -->
    <div class="article-meta mb-4">
      Danh mục: <span class="text-primary"><?= htmlspecialchars($categoryName) ?></span>
      • Tác giả: <?= htmlspecialchars($author) ?>
      • <time><?= htmlspecialchars($createdAt) ?></time>
    </div>

    <!-- Ảnh minh họa -->
    <?php if (!empty($images)): ?>
      <div class="row g-2 mb-4">
        <?php foreach ($images as $img): ?>
          <?php $src = ($img['media_url'] ?? ''); if ($src === '') continue; ?>
          <div class="col-6 col-md-4">
            <img
              src="<?= htmlspecialchars(rtrim($baseUrl, '/').'/'.$src) ?>"
              alt=""
              class="img-fluid"
              style="border-radius:8px"
            >
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Nội dung -->
    <div class="article-content mb-5">
      <?= nl2br(htmlspecialchars($content)) ?>
    </div>

    <!-- Bình luận -->
    <h2 class="h5 mt-5 mb-3">Bình luận</h2>

    <ul class="list-group mb-3" id="comment-list">
      <?php if (!empty($comments)): ?>
        <?php foreach ($comments as $c): ?>
          <li class="list-group-item">
            <div class="fw-semibold"><?= htmlspecialchars($c['username'] ?? 'Người dùng') ?></div>
            <div><?= nl2br(htmlspecialchars($c['content'] ?? '')) ?></div>
            <div class="text-muted small"><?= htmlspecialchars($c['created_at'] ?? '') ?></div>
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li class="list-group-item text-muted">Chưa có bình luận nào.</li>
      <?php endif; ?>
    </ul>

    <?php if (!empty($_SESSION['user_id'])): ?>
      <div class="card mb-5">
        <div class="card-body">
          <div class="mb-2">Thêm bình luận</div>
          <textarea id="comment-content" class="form-control mb-2" rows="3" placeholder="Nội dung..."></textarea>
          <button id="btn-send" class="btn btn-primary">Gửi</button>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        Vui lòng <a href="<?= htmlspecialchars(rtrim($baseUrl,'/').'/auth/login') ?>">đăng nhập</a> để bình luận.
      </div>
    <?php endif; ?>

  </div>
</div>

<script>
(function () {
  const baseUrl   = <?= json_encode((string)$baseUrl) ?>;
  const articleId = <?= (int)$articleId ?>;

  // helper: POST JSON (nếu dự án chưa có)
  async function postJSON(url, data) {
    const res = await fetch(url, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(data)
    });
    try { return await res.json(); } catch (_) { return {error: 'Bad response'}; }
  }

  <?php if (!empty($_SESSION['user_id'])): ?>
  const btn = document.getElementById('btn-send');
  if (btn) {
    btn.addEventListener('click', async () => {
      const ta = document.getElementById('comment-content');
      if (!ta) return;
      const content = ta.value.trim();
      if (!content) return;

      const api = baseUrl.replace(/\/$/, '') + '/api/comments';
      const res = await postJSON(api, { article_id: articleId, content });

      if (res && !res.error) {
        location.reload();
      } else {
        alert(res.error || 'Đã xảy ra lỗi. Vui lòng thử lại.');
      }
    });
  }
  <?php endif; ?>
})();
</script>
