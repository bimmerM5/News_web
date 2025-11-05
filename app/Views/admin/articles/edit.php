<h1 class="h4 mb-3">Edit Article</h1>
<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/articles/<?= (int)$article['article_id'] ?>/update" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($article['title']) ?>" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Summary</label>
        <textarea name="summary" class="form-control" rows="3"><?= htmlspecialchars($article['summary']) ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Content</label>
        <textarea name="content" class="form-control" rows="6"><?= htmlspecialchars($content ?? '') ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select">
            <?php foreach ($categories as $c): ?>
                <option value="<?= (int)$c['category_id'] ?>" <?= $c['category_id']==$article['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['category_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if (!empty($images)): ?>
    <div class="mb-3">
        <label class="form-label">Current Images</label>
        <div class="row g-2">
            <?php foreach ($images as $img): ?>
                <div class="col-6 col-md-3"><img src="<?= htmlspecialchars($baseUrl . '/' . $img['media_url']) ?>" style="width:100%;border-radius:6px"></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <div class="mb-3">
        <label class="form-label">Add Images</label>
        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
    </div>
    <button class="btn btn-primary">Update</button>
</form>
