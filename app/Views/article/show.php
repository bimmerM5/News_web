<div class="row">
    <div class="col-lg-9 mx-auto">
        <h1 class="article-title display-6 mb-2"><?= htmlspecialchars($article['title']) ?></h1>
        <div class="article-meta mb-4">Danh mục: <?= htmlspecialchars($article['category_name'] ?? '—') ?> • Tác giả: <?= htmlspecialchars($article['username'] ?? '—') ?> • <?= htmlspecialchars($article['created_at']) ?></div>

        <?php if (!empty($images)): ?>
        <div class="row g-2 mb-4">
            <?php foreach ($images as $img): ?>
                <div class="col-6 col-md-4"><img src="<?= htmlspecialchars($baseUrl . '/' . $img['media_url']) ?>" style="width:100%;border-radius:8px"></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="article-content mb-5">
            <?= nl2br(htmlspecialchars($articleContent ?? '')) ?>
        </div>

        <h2 class="h5 mt-5 mb-3">Bình luận</h2>
        <ul class="list-group mb-3" id="comment-list">
            <?php foreach ($comments as $c): ?>
                <li class="list-group-item">
                    <div class="fw-semibold"><?= htmlspecialchars($c['username']) ?></div>
                    <div><?= nl2br(htmlspecialchars($c['content'])) ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($c['created_at']) ?></div>
                </li>
            <?php endforeach; ?>
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
        <div class="alert alert-info">Vui lòng <a href="<?= htmlspecialchars($baseUrl) ?>/auth/login">đăng nhập</a> để bình luận.</div>
        <?php endif; ?>
    </div>
</div>
<script>
const baseUrl = '<?= htmlspecialchars($baseUrl) ?>';
const articleId = <?= (int)$article['article_id'] ?>;

<?php if (!empty($_SESSION['user_id'])): ?>
document.getElementById('btn-send').addEventListener('click', async () => {
  const content = document.getElementById('comment-content').value.trim();
  if (!content) return;
  const res = await postJSON(baseUrl + '/api/comments', {article_id: articleId, content});
  if (!res.error){
    location.reload();
  } else {
    alert(res.error || 'Error');
  }
});
<?php endif; ?>
</script>
