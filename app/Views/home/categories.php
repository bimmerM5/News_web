<div class="py-3">
    <h1 class="display-6 hero-title mb-4">Danh mục</h1>
    <?php if (empty($rows)): ?>
        <div class="alert alert-info">Chưa có danh mục.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($rows as $r): ?>
                <div class="col-sm-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="mb-1"><a href="<?= htmlspecialchars($baseUrl) ?>/category/<?= (int)$r['category_id'] ?>"><?= htmlspecialchars($r['category_name']) ?></a></h5>
                            <div class="text-muted small mb-2"><?= htmlspecialchars($r['description'] ?? '') ?></div>
                            <div class="mt-auto text-muted">Bài viết: <?= (int)$r['total'] ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
