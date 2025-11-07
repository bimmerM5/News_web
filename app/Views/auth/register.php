<div class="row justify-content-center">
    <div class="col-md-5">
        <h1 class="h4 mb-3">Đăng ký</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="register" novalidate>
            <?php $errs = $errors ?? []; $old = $old ?? []; ?>
            <div class="mb-3">
                <label class="form-label">Tên đăng nhập</label>
                <input class="form-control<?= !empty($errs['username']) ? ' is-invalid' : '' ?>" type="text" name="username" id="register-username" value="<?= htmlspecialchars($old['username'] ?? '') ?>" required>
                <div class="invalid-feedback" id="username-feedback"><?= htmlspecialchars($errs['username'] ?? '') ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control<?= !empty($errs['email']) ? ' is-invalid' : '' ?>" type="email" name="email" id="register-email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                <div class="invalid-feedback" id="email-feedback"><?= htmlspecialchars($errs['email'] ?? '') ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <div class="input-group">
                    <input class="form-control" type="password" name="password" id="register-password" required>
                    <button class="btn btn-outline-secondary password-toggle" type="button" data-target="register-password" aria-label="Hiện mật khẩu">
                        <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5 0-9.27 3.11-11 7 1.73 3.89 6 7 11 7s9.27-3.11 11-7c-1.73-3.89-6-7-11-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Zm0-2.5A2.5 2.5 0 1 0 12 8a2.5 2.5 0 0 0 0 5Z"/></svg>
                        <svg class="eye-closed d-none" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.1 3.51 3.5 2.1l18.4 18.4-1.41 1.41-3.2-3.2A12.6 12.6 0 0 1 12 19c-5 0-9.27-3.11-11-7a13.6 13.6 0 0 1 5.02-5.7L2.1 3.51ZM7.14 8.55A11.4 11.4 0 0 0 3.1 12c1.73 3.89 6 7 11 7 1.63 0 3.18-.3 4.6-.85l-2.36-2.36A5 5 0 0 1 9.21 10.5L7.14 8.55Zm8.9 5.9-1.52-1.52a2.5 2.5 0 0 1-3.45-3.45L9.54 7.95a5 5 0 0 1 6.5 6.5Z"/></svg>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Xác nhận mật khẩu</label>
                <div class="input-group has-validation">
                    <input class="form-control<?= !empty($errs['confirm_password']) ? ' is-invalid' : '' ?>" type="password" name="confirm_password" id="register-password-confirm" required>
                    <button class="btn btn-outline-secondary password-toggle" type="button" data-target="register-password-confirm" aria-label="Hiện mật khẩu">
                        <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5 0-9.27 3.11-11 7 1.73 3.89 6 7 11 7s9.27-3.11 11-7c-1.73-3.89-6-7-11-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Zm0-2.5A2.5 2.5 0 1 0 12 8a2.5 2.5 0 0 0 0 5Z"/></svg>
                        <svg class="eye-closed d-none" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.1 3.51 3.5 2.1l18.4 18.4-1.41 1.41-3.2-3.2A12.6 12.6 0 0 1 12 19c-5 0-9.27-3.11-11-7a13.6 13.6 0 0 1 5.02-5.7L2.1 3.51ZM7.14 8.55A11.4 11.4 0 0 0 3.1 12c1.73 3.89 6 7 11 7 1.63 0 3.18-.3 4.6-.85l-2.36-2.36A5 5 0 0 1 9.21 10.5L7.14 8.55Zm8.9 5.9-1.52-1.52a2.5 2.5 0 0 1-3.45-3.45L9.54 7.95a5 5 0 0 1 6.5 6.5Z"/></svg>
                    </button>
                    <div class="invalid-feedback" id="confirm-feedback"><?= htmlspecialchars($errs['confirm_password'] ?? '') ?></div>
                </div>
            </div>
            <button class="btn btn-primary w-100">Đăng ký</button>
        </form>
        <div class="mt-2"><a href="login">Đã có tài khoản? Đăng nhập</a></div>
    </div>
 </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var passwordInput = document.getElementById('register-password');
    var confirmInput = document.getElementById('register-password-confirm');
    var usernameInput = document.getElementById('register-username');
    var emailInput = document.getElementById('register-email');
    var form = document.querySelector('form[method="post"][action="register"]');

    // Toggle eye buttons
    document.querySelectorAll('.password-toggle').forEach(function(btn){
        btn.addEventListener('click', function(){
            var id = btn.getAttribute('data-target');
            var inp = document.getElementById(id);
            if(!inp) return;
            var show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            var open = btn.querySelector('.eye-open');
            var closed = btn.querySelector('.eye-closed');
            if(open && closed){
                if(show){ open.classList.add('d-none'); closed.classList.remove('d-none'); }
                else { closed.classList.add('d-none'); open.classList.remove('d-none'); }
            }
            btn.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        });
    });

    function validateMatch() {
        if (passwordInput && confirmInput) {
            if (confirmInput.value !== '' && passwordInput.value !== confirmInput.value) {
                confirmInput.classList.add('is-invalid');
                var fb = document.getElementById('confirm-feedback');
                if (fb) fb.textContent = 'Mật khẩu không giống';
            } else {
                confirmInput.classList.remove('is-invalid');
                var fb2 = document.getElementById('confirm-feedback');
                if (fb2) fb2.textContent = '';
            }
        }
    }
    if (passwordInput && confirmInput) {
        passwordInput.addEventListener('input', validateMatch);
        confirmInput.addEventListener('input', validateMatch);
    }

    if (form && passwordInput && confirmInput) {
        form.addEventListener('submit', function (e) {
            if (passwordInput.value !== confirmInput.value) {
                e.preventDefault();
                confirmInput.classList.add('is-invalid');
                var fb = document.getElementById('confirm-feedback');
                if (fb) fb.textContent = 'Mật khẩu không giống';
            }
        });
    }

    // --------- Live availability checks for username & email ---------
    var baseUrl = '<?= htmlspecialchars($baseUrl ?? '') ?>';
    function debounce(fn, delay){
        var t; return function(){ var args=arguments, ctx=this; clearTimeout(t); t=setTimeout(function(){ fn.apply(ctx,args); }, delay||350); };
    }
    function setInvalid(input, feedbackEl, message){
        if(!input || !feedbackEl) return; input.classList.add('is-invalid'); feedbackEl.textContent = message||''; updateSubmitState();
    }
    function clearInvalid(input, feedbackEl){
        if(!input || !feedbackEl) return; input.classList.remove('is-invalid'); if(!feedbackEl.dataset.server){ feedbackEl.textContent=''; } updateSubmitState();
    }
    function updateSubmitState(){
        if(!form) return;
        var btn = form.querySelector('button[type="submit"]') || form.querySelector('button');
        if(!btn) return;
        btn.disabled = !!document.querySelector('.form-control.is-invalid');
    }
    function checkAvailability(type, value){
        var url = baseUrl + '/api/check-availability?type=' + encodeURIComponent(type) + '&value=' + encodeURIComponent(value);
        return fetch(url, {headers:{'Accept':'application/json'}}).then(function(r){return r.json();});
    }
    var onUserChange = debounce(function(){
        var v = (usernameInput && usernameInput.value || '').trim();
        var fb = document.getElementById('username-feedback');
        if(!v){ clearInvalid(usernameInput, fb); return; }
        checkAvailability('username', v).then(function(res){
            if(res && res.exists){ setInvalid(usernameInput, fb, 'Tài khoản đã tồn tại'); }
            else { clearInvalid(usernameInput, fb); }
        }).catch(function(){ /* ignore */ });
    }, 400);
    var onEmailChange = debounce(function(){
        var v = (emailInput && emailInput.value || '').trim();
        var fb = document.getElementById('email-feedback');
        if(!v){ clearInvalid(emailInput, fb); return; }
        checkAvailability('email', v).then(function(res){
            if(res && res.exists){ setInvalid(emailInput, fb, 'Email đã được sử dụng'); }
            else { clearInvalid(emailInput, fb); }
        }).catch(function(){ /* ignore */ });
    }, 400);
    if(usernameInput){ usernameInput.addEventListener('input', onUserChange); usernameInput.addEventListener('blur', onUserChange); }
    if(emailInput){ emailInput.addEventListener('input', onEmailChange); emailInput.addEventListener('blur', onEmailChange); }
    updateSubmitState();
});
</script>
