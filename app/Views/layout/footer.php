</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  const STORAGE_KEY = 'newsweb-theme';   
  const COOKIE_KEY  = 'newsweb-theme';
  const root = document.documentElement;
  const btn  = document.getElementById('theme-toggle');

  function getCookie(name) {
    const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
  }
  function setCookie(name, value) {
    document.cookie = name + '=' + encodeURIComponent(value) + ';path=/;max-age=31536000'; // 1 năm
  }
  function applyTheme(theme) {
    root.setAttribute('data-theme', theme);
    if (btn) {
      const isDark = theme === 'dark';
      btn.setAttribute('aria-pressed', String(isDark));
      btn.textContent = isDark ? '🌙 Dark' : '🌞 Light';
      btn.classList.toggle('btn-outline-light', !isDark);
      btn.classList.toggle('btn-outline-secondary', isDark);
    }
  }

  let theme = localStorage.getItem(STORAGE_KEY);

  if (!theme) {
    theme = getCookie(COOKIE_KEY);
  }
  if (!theme) {
    theme = root.getAttribute('data-theme');
  }
  if (!theme) {
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    theme = prefersDark ? 'dark' : 'light';
  }

  applyTheme(theme);
  try { localStorage.setItem(STORAGE_KEY, theme); } catch (e) {}
  setCookie(COOKIE_KEY, theme);

  if (btn) {
    btn.addEventListener('click', function () {
      const current = root.getAttribute('data-theme') || 'dark';
      const next = current === 'dark' ? 'light' : 'dark';
      applyTheme(next);
      try { localStorage.setItem(STORAGE_KEY, next); } catch (e) {}
      setCookie(COOKIE_KEY, next);
    });
  }
})();
</script>

</body>
</html>
