(function () {
  var root = document.documentElement, btn = document.getElementById('theme');
  function paint() { if (btn) btn.textContent = root.dataset.theme === 'dark' ? '☀' : '☾'; }
  paint();
  if (btn) btn.addEventListener('click', function () {
    root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    try { localStorage.setItem('theme', root.dataset.theme); } catch (e) {}
    paint();
  });
  var q = document.getElementById('q'), cards = document.querySelectorAll('.reel'), empty = document.getElementById('empty');
  var chips = document.querySelectorAll('.chip'), cat = '';
  function apply() {
    var v = q ? q.value.trim().toLowerCase() : '', shown = 0;
    cards.forEach(function (c) {
      var m = c.dataset.t.indexOf(v) > -1 && (!cat || c.dataset.c === cat);
      c.hidden = !m; if (m) shown++;
    });
    if (empty) { empty.hidden = shown > 0; empty.textContent = 'No products match your search.'; }
  }
  if (q) q.addEventListener('input', apply);
  chips.forEach(function (b) {
    b.addEventListener('click', function () {
      chips.forEach(function (o) { o.classList.remove('on'); });
      b.classList.add('on'); cat = b.dataset.c; apply();
    });
  });
  // Reels: play only what is on screen
  var vids = document.querySelectorAll('.reel video');
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (en) { if (en.isIntersecting) en.target.play().catch(function () {}); else en.target.pause(); });
    }, { threshold: 0.6 });
    vids.forEach(function (v) { io.observe(v); });
  }
  document.querySelectorAll('.snd').forEach(function (b) {
    b.addEventListener('click', function () {
      var v = b.parentNode.querySelector('video'), on = v.muted;
      vids.forEach(function (o) { o.muted = true; o.parentNode.querySelector('.snd').textContent = '🔇'; });
      v.muted = !on; b.textContent = on ? '🔊' : '🔇';
    });
  });
})();
(function () {
  // Shop now dropdown
  function closeAll(except) {
    document.querySelectorAll('.shopbtn[aria-expanded=true]').forEach(function (b) {
      if (b !== except) { b.setAttribute('aria-expanded', 'false'); b.nextElementSibling.hidden = true; }
    });
  }
  document.addEventListener('click', function (e) {
    var b = e.target.closest('.shopbtn');
    if (b) {
      var open = b.getAttribute('aria-expanded') === 'true';
      closeAll(b); b.setAttribute('aria-expanded', String(!open)); b.nextElementSibling.hidden = open; return;
    }
    if (!e.target.closest('.menu')) closeAll();
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAll(); });
  // Admin: add / remove platform rows
  var wrap = document.getElementById('links'), add = document.getElementById('addlink');
  if (wrap && add) {
    add.addEventListener('click', function () {
      var r = wrap.lastElementChild.cloneNode(true);
      r.querySelectorAll('input').forEach(function (i) { i.value = ''; i.required = false; });
      wrap.appendChild(r); r.querySelector('input').focus();
    });
    wrap.addEventListener('click', function (e) {
      if (!e.target.classList.contains('x')) return;
      var row = e.target.parentNode;
      if (wrap.children.length > 1) { row.remove(); wrap.firstElementChild.querySelector('input[type=url]').required = true; }
      else row.querySelectorAll('input').forEach(function (i) { i.value = ''; });
    });
  }
})();
