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
        <label class="form-label">Ảnh hiện có</label>
        <div class="row g-3">
            <?php foreach ($images as $img): ?>
            <div class="col-12">
                <div class="card p-2">
                    <div class="d-flex align-items-start gap-3">
                        <img src="<?= htmlspecialchars($baseUrl . '/' . $img['media_url']) ?>" style="width:140px;height:90px;object-fit:cover;border-radius:6px">
                        <div class="flex-grow-1 row g-2">
                            <input type="hidden" name="existing_media_id[]" value="<?= (int)($img['media_id'] ?? 0) ?>">
                            <div class="col-md-4">
                                <label class="form-label small">Kích thước</label>
                                <select name="existing_size[]" class="form-select form-select-sm">
                                    <?php $sz = htmlspecialchars($img['size_class'] ?? 'img-medium'); ?>
                                    <option value="img-small" <?= $sz==='img-small'?'selected':''; ?>>Nhỏ</option>
                                    <option value="img-medium" <?= $sz==='img-medium'?'selected':''; ?>>Trung bình</option>
                                    <option value="img-large" <?= $sz==='img-large'?'selected':''; ?>>Lớn</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Căn chỉnh</label>
                                <?php $al = htmlspecialchars($img['align_class'] ?? 'img-center'); ?>
                                <select name="existing_align[]" class="form-select form-select-sm">
                                    <option value="img-left" <?= $al==='img-left'?'selected':''; ?>>Trái</option>
                                    <option value="img-center" <?= $al==='img-center'?'selected':''; ?>>Giữa</option>
                                    <option value="img-right" <?= $al==='img-right'?'selected':''; ?>>Phải</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Caption</label>
                                <input name="existing_caption[]" class="form-control form-control-sm" value="<?= htmlspecialchars($img['caption'] ?? '') ?>" placeholder="Mô tả ngắn cho ảnh">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <div class="mb-3">
        <label class="form-label">Thêm ảnh</label>
        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
        <div id="image-options" class="row g-3 mt-2"></div>
    </div>

    <?php if (!empty($images)): ?>
    <div class="mb-3">
        <label class="form-label">Chọn ảnh để xóa</label>
        <div class="row g-2">
            <?php foreach ($images as $img): ?>
                <div class="col-6 col-md-3 text-center">
                    <img src="<?= htmlspecialchars($baseUrl . '/' . $img['media_url']) ?>" style="width:100%;height:120px;object-fit:cover;border-radius:6px">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="delete_media_id[]" value="<?= (int)($img['media_id'] ?? 0) ?>" id="del<?= (int)($img['media_id'] ?? 0) ?>">
                        <label class="form-check-label small" for="del<?= (int)($img['media_id'] ?? 0) ?>">Xóa ảnh này</label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <button class="btn btn-primary">Update</button>
</form>

<script>
// Build per-image options for newly added images (same UI as create)
(function(){
  const input = document.querySelector('input[name="images[]"]');
  const wrap = document.getElementById('image-options');
  if (!input || !wrap) return;
  input.addEventListener('change', () => {
    wrap.innerHTML = '';
    const files = Array.from(input.files || []);
    files.forEach((file, idx) => {
      const id = 'imgopt_' + idx;
      const col = document.createElement('div');
      col.className = 'col-12';
      col.innerHTML = `
        <div class="card p-2">
          <div class="d-flex align-items-start gap-3">
            <img id="${id}" class="rounded" style="width:140px;height:90px;object-fit:cover" alt="preview">
            <div class="flex-grow-1 row g-2">
              <div class="col-md-4">
                <label class="form-label small">Kích thước</label>
                <select name="image_size[]" class="form-select form-select-sm">
                  <option value="img-small">Nhỏ</option>
                  <option value="img-medium" selected>Trung bình</option>
                  <option value="img-large">Lớn</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small">Căn chỉnh</label>
                <select name="image_align[]" class="form-select form-select-sm">
                  <option value="img-left">Trái</option>
                  <option value="img-center" selected>Giữa</option>
                  <option value="img-right">Phải</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label small">Caption</label>
                <input name="image_caption[]" class="form-control form-control-sm" placeholder="Mô tả ngắn cho ảnh">
              </div>
            </div>
          </div>
        </div>`;
      wrap.appendChild(col);
      const img = document.getElementById(id);
      const reader = new FileReader();
      reader.onload = e => { img.src = e.target.result; };
      reader.readAsDataURL(file);
    });
  });
})();
</script>
