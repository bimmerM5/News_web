<div class="row justify-content-center">
    <div class="col-md-5">
        <h1 class="h4 mb-3">Đăng ký</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="register">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input class="form-control" type="text" name="username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input class="form-control" type="password" name="password" required>
            </div>
            <button class="btn btn-primary w-100">Đăng ký</button>
        </form>
        <div class="mt-2"><a href="login">Đã có tài khoản? Đăng nhập</a></div>
    </div>
</div>
