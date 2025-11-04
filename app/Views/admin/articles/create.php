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
        <div id="image-options" class="row g-3 mt-2"></div>
    </div>
    <button class="btn btn-primary">Save</button>
</form>

<script>
// Build per-image options (size, align, caption) for newly selected files
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
