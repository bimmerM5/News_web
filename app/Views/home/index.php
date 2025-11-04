<div class="py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <h1 class="display-6 hero-title mb-3 mb-sm-0">Bài viết mới</h1>
        <form method="get" class="d-flex align-items-center" action="">
            <label class="me-2 text-muted">Danh mục</label>
            <select name="cat" class="form-select" onchange="this.form.submit()">
                <option value="0">Tất cả</option>
                <?php foreach (($categories ?? []) as $c): ?>
                    <option value="<?= (int)$c['category_id'] ?>" <?= (($selectedCat ?? 0) == $c['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <?php if (empty($articles)): ?>
        <div class="alert alert-info">Chưa có bài viết.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($articles as $a): ?>
            <div class="col-sm-6 col-lg-4">
                <div class="card h-100">
                    <?php if (!empty($a['thumb'])): ?>
                    <img class="article-thumb" src="<?= htmlspecialchars($baseUrl) ?>/<?= htmlspecialchars($a['thumb']) ?>" alt="<?= htmlspecialchars($a['title']) ?>">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge badge-cat"><?= htmlspecialchars($a['category_name'] ?? '—') ?></span>
                        </div>
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
                        <a class="page-link" href="?cat=<?= (int)($selectedCat ?? 0) ?>&page=<?= $p ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
