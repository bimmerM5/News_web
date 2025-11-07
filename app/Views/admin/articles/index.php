<div class="admin-articles"><div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Articles</h1>
    <a class="btn btn-primary" href="<?= htmlspecialchars($baseUrl) ?>/admin/articles/create">Create</a>
</div><div class="table-responsive"><table class="table table-bordered mb-0">
    <thead>
    <tr><th>ID</th><th>Title</th><th>Status</th><th>Category</th><th>Created</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= (int)$r['article_id'] ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['status']) ?></td>
            <td><?= htmlspecialchars($r['category_name'] ?? '—') ?></td>
            <td><?= htmlspecialchars($r['created_at']) ?></td>
            <td>
                <a class="btn btn-sm btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/admin/articles/<?= (int)$r['article_id'] ?>/edit">Edit</a>
                <?php if ($r['status'] !== 'published'): ?>
                <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/articles/<?= (int)$r['article_id'] ?>/publish" style="display:inline" onsubmit="return confirm('Publish this article?')">
                    <button class="btn btn-sm btn-success">Publish</button>
                </form>
                <?php endif; ?>
                <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/articles/<?= (int)$r['article_id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete?')">
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div></div>

