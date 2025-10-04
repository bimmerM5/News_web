<h1 class="h4 mb-3">Create Category</h1>
<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/categories/store">
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="category_name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
    </div>
    <button class="btn btn-primary">Save</button>
</form>
