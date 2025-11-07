<div class="row justify-content-center">
    <div class="col-md-4">
        <h1 class="h4 mb-3">Đăng nhập</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="login">
            <div class="mb-3">
                <label class="form-label">Tên đăng nhập</label>
                <input class="form-control" type="text" name="username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input class="form-control" type="password" name="password" required>
            </div>
            <button class="btn btn-primary w-100">Đăng nhập</button>
        </form>
        <div class="mt-2"><a href="register">Chưa có tài khoản? Đăng ký</a></div>
    </div>
</div>
