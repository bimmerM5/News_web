</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function postJSON(url, data){
  const res = await fetch(url,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
  return res.json();
}
</script>
<script>
(function(){
  const key = 'theme';
  const root = document.documentElement;

  function setTheme(t){
    root.setAttribute('data-theme', t);
    try{ localStorage.setItem(key, t); }catch(e){}
  }

  function getTheme(){
    return root.getAttribute('data-theme') || 'light';
  }

  function updateButton(btn, theme){
    if(!btn) return;
    // Update label/text for clarity
    const isDark = theme === 'dark';
    btn.textContent = isDark ? 'Sáng' : 'Tối';
    btn.setAttribute('aria-label', isDark ? 'Chuyển sang giao diện sáng' : 'Chuyển sang giao diện tối');
  }

  function init(){
    const btn = document.getElementById('themeToggle');
    if(!btn) return;
    updateButton(btn, getTheme());
    btn.addEventListener('click', function(){
      const next = getTheme() === 'dark' ? 'light' : 'dark';
      setTheme(next);
      updateButton(btn, next);
    });

    // If no explicit preference saved, follow system changes
    try{
      const mq = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)');
      if(mq && typeof mq.addEventListener === 'function'){
        mq.addEventListener('change', function(e){
          // Respect saved preference; only auto-switch when user hasn't chosen
          if(!localStorage.getItem(key)){
            setTheme(e.matches ? 'dark' : 'light');
            updateButton(btn, getTheme());
          }
        });
      }
    }catch(e){}
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', init);
  }else{
    init();
  }
})();
</script>
<script>
// Transform admin links into a dropdown for a cleaner header
(function(){
  function enhanceHeader(){
    try{
      var nav = document.querySelector('nav .navbar-collapse .navbar-nav');
      if(!nav) return;
      var links = nav.querySelectorAll('a.nav-link');
      var articlesItem = null, categoriesItem = null;
      var searchLink = null;
      links.forEach(function(a){
        try{
          var u = new URL(a.href, location.origin);
          if(u.pathname.endsWith('/admin/articles')) articlesItem = a.closest('li');
          if(u.pathname.endsWith('/admin/categories')) categoriesItem = a.closest('li');
          if(u.pathname.endsWith('/search')) searchLink = a;
        }catch(e){}
      });
      // Only show admin menu for admin user (bimmer). Hide for normal users like damhieu2005.
      try{
        var greeting = nav.querySelector('.nav-item.text-white');
        var username = '';
        if(greeting){
          var t = greeting.textContent || '';
          var m = t.match(/Xin ch[^,]*,\s*(\S+)/i);
          if(m && m[1]) username = (m[1]+'').trim();
        }
        var isAdmin = username.toLowerCase() === 'bimmer';
        if(!isAdmin){
          if(articlesItem) articlesItem.remove();
          if(categoriesItem) categoriesItem.remove();
          return;
        }
      }catch(e){}
      if(!articlesItem && !categoriesItem) return; // nothing to do
      // Avoid duplicating if already converted
      if(nav.querySelector('#adminMenu')) return;

      var first = articlesItem || categoriesItem;
      var activeChild = (first && first.querySelector('a.nav-link.active')) || (categoriesItem && categoriesItem.querySelector('a.nav-link.active'));

      var li = document.createElement('li');
      li.className = 'nav-item dropdown';
      var toggle = document.createElement('a');
      toggle.className = 'nav-link dropdown-toggle' + (activeChild ? ' active' : '');
      toggle.href = '#';
      toggle.id = 'adminMenu';
      toggle.setAttribute('role','button');
      toggle.setAttribute('data-bs-toggle','dropdown');
      toggle.setAttribute('aria-expanded','false');
      toggle.textContent = 'Quản lí';
      var menu = document.createElement('ul');
      menu.className = 'dropdown-menu dropdown-menu-end';
      menu.setAttribute('aria-labelledby','adminMenu');

      function addItem(sourceLi, textFallback){
        if(!sourceLi) return;
        var link = sourceLi.querySelector('a');
        if(!link) return;
        var item = document.createElement('li');
        var a = document.createElement('a');
        a.className = 'dropdown-item' + (link.classList.contains('active') ? ' active' : '');
        a.href = link.href;
        a.textContent = link.textContent && link.textContent.trim() ? link.textContent.trim() : textFallback;
        item.appendChild(a);
        menu.appendChild(item);
      }

      addItem(articlesItem, 'Bài viết');
      addItem(categoriesItem, 'Danh mục');

      li.appendChild(toggle);
      li.appendChild(menu);
      nav.insertBefore(li, first);

      if(articlesItem) articlesItem.remove();
      if(categoriesItem) categoriesItem.remove();

      // Convert the search nav link into an icon-only button
      if(searchLink){
        searchLink.classList.add('nav-icon','search-icon');
        searchLink.setAttribute('aria-label','Tìm kiếm');
        searchLink.setAttribute('title','Tìm kiếm');
        // Clear text then inject inline SVG (magnifying glass)
        try{ searchLink.textContent=''; }catch(e){}
        var svgNS='http://www.w3.org/2000/svg';
        var svg=document.createElementNS(svgNS,'svg');
        svg.setAttribute('viewBox','0 0 24 24');
        svg.setAttribute('aria-hidden','true');
        svg.style.width='18px'; svg.style.height='18px';
        var path=document.createElementNS(svgNS,'path');
        path.setAttribute('d','M10.5 3a7.5 7.5 0 1 1 0 15 7.5 7.5 0 0 1 0-15zm0 2a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11zm8.53 12.47 3.25 3.25a1 1 0 0 1-1.41 1.41l-3.25-3.25a1 1 0 0 1 1.41-1.41z');
        svg.appendChild(path);
        searchLink.appendChild(svg);
      }
    }catch(e){}
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', enhanceHeader);
  }else{
    enhanceHeader();
  }
})();
</script>
</body>
</html>
