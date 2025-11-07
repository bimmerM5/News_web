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
                <input class="form-control" type="password" name="password" id="login-password" required>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="show-password-login">
                    <label class="form-check-label" for="show-password-login">Hiện mật khẩu</label>
                </div>
            </div>
            <button class="btn btn-primary w-100">Đăng nhập</button>
        </form>
        <div class="mt-2"><a href="register">Chưa có tài khoản? Đăng ký</a></div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var checkbox = document.getElementById('show-password-login');
    var passwordInput = document.getElementById('login-password');
    if (checkbox && passwordInput) {
        checkbox.addEventListener('change', function () {
            passwordInput.type = this.checked ? 'text' : 'password';
        });
    }
});
</script>
