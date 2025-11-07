<div class="admin-categories">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4">Danh mục</h1>
        <a class="btn btn-primary" href="<?= htmlspecialchars($baseUrl) ?>/admin/categories/create">Tạo mới</a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead>
            <tr><th>ID</th><th>Tên</th><th>Mô tả</th><th>Thao tác</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td data-label="ID"><?= (int)$r['category_id'] ?></td>
                    <td data-label="Tên"><?= htmlspecialchars($r['category_name']) ?></td>
                    <td data-label="Mô tả"><?= htmlspecialchars($r['description']) ?></td>
                    <td data-label="Thao tác">
                        <a class="btn btn-sm btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/admin/categories/<?= (int)$r['category_id'] ?>/edit">Sửa</a>
                        <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/categories/<?= (int)$r['category_id'] ?>/delete" style="display:inline" onsubmit="return confirm('Xóa?')">
                            <button class="btn btn-sm btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
