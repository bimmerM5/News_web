<h1 class="h4 mb-3">Create Article</h1>
<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/articles/store" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Summary</label>
        <textarea name="summary" class="form-control" rows="3"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Content</label>
        <textarea name="content" class="form-control" rows="6"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select">
            <?php foreach ($categories as $c): ?>
                <option value="<?= (int)$c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Images</label>
        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
    </div>
    <button class="btn btn-primary">Save</button>
</form>
