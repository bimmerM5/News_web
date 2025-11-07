<div class="admin-articles"><div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Bài viết</h1>
    <a class="btn btn-primary" href="<?= htmlspecialchars($baseUrl) ?>/admin/articles/create">Tạo mới</a>
</div><div class="table-responsive"><table class="table table-bordered mb-0">
    <thead>
    <tr><th>ID</th><th>Tiêu đề</th><th>Trạng thái</th><th>Danh mục</th><th>Ngày tạo</th><th>Thao tác</th></tr>
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
                <a class="btn btn-sm btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/admin/articles/<?= (int)$r['article_id'] ?>/edit">Sửa</a>
                <?php if ($r['status'] !== 'published'): ?>
                <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/articles/<?= (int)$r['article_id'] ?>/publish" style="display:inline" onsubmit="return confirm('Xuất bản bài viết này?')">
                    <button class="btn btn-sm btn-success">Xuất bản</button>
                </form>
                <?php endif; ?>
                <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/articles/<?= (int)$r['article_id'] ?>/delete" style="display:inline" onsubmit="return confirm('Xóa?')">
                    <button class="btn btn-sm btn-danger">Xóa</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div></div>

