<div class="admin-categories">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4">Categories</h1>
        <a class="btn btn-primary" href="<?= htmlspecialchars($baseUrl) ?>/admin/categories/create">Create</a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead>
            <tr><th>ID</th><th>Name</th><th>Description</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td data-label="ID"><?= (int)$r['category_id'] ?></td>
                    <td data-label="Name"><?= htmlspecialchars($r['category_name']) ?></td>
                    <td data-label="Description"><?= htmlspecialchars($r['description']) ?></td>
                    <td data-label="Actions">
                        <a class="btn btn-sm btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/admin/categories/<?= (int)$r['category_id'] ?>/edit">Edit</a>
                        <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/categories/<?= (int)$r['category_id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete?')">
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
