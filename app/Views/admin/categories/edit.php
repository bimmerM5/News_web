<h1 class="h4 mb-3">Sửa danh mục</h1>
<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/categories/<?= (int)$row['category_id'] ?>/update">
    <div class="mb-3">
        <label class="form-label">Tên</label>
        <input type="text" name="category_name" value="<?= htmlspecialchars($row['category_name']) ?>" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Mô tả</label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($row['description']) ?></textarea>
    </div>
    <button class="btn btn-primary">Cập nhật</button>
</form>
