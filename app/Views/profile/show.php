<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card mb-4">
            <div class="card-body d-flex align-items-center">
                <img src="<?= htmlspecialchars($user['avatar_url'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($user['username'])) ?>" alt="Ảnh đại diện" style="width:72px;height:72px;border-radius:50%;object-fit:cover" class="me-3">
                <div>
                    <div class="h5 mb-0"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></div>
                    <div class="text-muted small">@<?= htmlspecialchars($user['username']) ?></div>
                </div>
            </div>
        </div>

        <h2 class="h5 mb-3">Bài viết của người dùng</h2>
        <?php if (empty($articles)): ?>
            <div class="alert alert-info">Chưa có bài viết.</div>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($articles as $a): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="<?= htmlspecialchars($baseUrl) ?>/article/<?= (int)$a['article_id'] ?>"><?= htmlspecialchars($a['title']) ?></a>
                    <span class="badge bg-secondary"><?= htmlspecialchars($a['status']) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
