<div class="py-3">
    <h1 class="display-6 hero-title mb-4">Danh mục: <?= htmlspecialchars($category['category_name'] ?? '—') ?></h1>
    <?php if (empty($articles)): ?>
        <div class="alert alert-info">Chưa có bài viết.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($articles as $a): ?>
            <div class="col-sm-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-2"><a href="<?= htmlspecialchars($baseUrl) ?>/article/<?= (int)$a['article_id'] ?>"><?= htmlspecialchars($a['title']) ?></a></h5>
                        <div class="text-muted small mb-3"><?= htmlspecialchars($a['created_at']) ?></div>
                        <p class="card-text flex-grow-1"><?= nl2br(htmlspecialchars($a['summary'] ?? '')) ?></p>
                        <a class="btn btn-primary mt-3" href="<?= htmlspecialchars($baseUrl) ?>/article/<?= (int)$a['article_id'] ?>">Đọc tiếp</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (($pages ?? 1) > 1): ?>
        <nav class="mt-4">
            <ul class="pagination">
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <li class="page-item <?= ($p == ($page ?? 1)) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
