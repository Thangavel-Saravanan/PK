<?php require 'config.php'; require 'helpers.php';
$items = db()->query('SELECT * FROM products ORDER BY id DESC')->fetchAll();
$L = []; foreach (db()->query('SELECT id, product_id, store FROM product_links ORDER BY id') as $r) $L[$r['product_id']][] = $r; ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(BRAND) ?> — Fashion picks</title>
<script>document.documentElement.dataset.theme=localStorage.getItem('theme')||(matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light')</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;800&display=swap" rel="stylesheet">
<link rel="icon" href="assets/logo.png">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="bar">
  <input id="q" type="search" placeholder="Search products" aria-label="Search products">
  <button id="theme" class="icon" aria-label="Switch light or dark theme"></button>
  <a class="link" href="admin/login.php"><?= is_admin() ? 'Dashboard' : 'Admin' ?></a>
  <a class="brand" href="./"><img class="logo" src="assets/logo.png" alt=""><span><?= e(BRAND) ?></span></a>
</header>
<section class="hero">
  <div>
    <h1>Love it.<br>Buy it.</h1>
    <p>Watch the look, tap Shop now, and pick the store you like best.</p>
  </div>
  <img class="hero-logo" src="assets/Person.png" alt="<?= e(BRAND) ?> logo">
</section>
<main>
  <?php $cats = categories(); if ($cats): ?>
  <nav class="chips" id="chips" aria-label="Categories">
    <button type="button" class="chip on" data-c="">All</button>
    <?php foreach ($cats as $c): ?><button type="button" class="chip" data-c="<?= e(strtolower($c)) ?>"><?= e($c) ?></button><?php endforeach; ?>
  </nav>
  <?php endif; ?>
  <div class="grid" id="grid">
  <?php foreach ($items as $p): ?>
    <article class="reel" data-t="<?= e(strtolower($p['title'])) ?>" data-c="<?= e(strtolower($p['category'] ?? '')) ?>">
      <?php if ($p['video']): ?>
        <video src="<?= e(media($p['video'])) ?>" <?= $p['image_url'] ? 'poster="'.e(media((string)$p['image_url'])).'"' : '' ?> muted loop playsinline preload="metadata"></video>
        <button class="snd" aria-label="Turn sound on or off">🔇</button>
      <?php else: ?>
        <img src="<?= e(media((string)$p['image_url'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
      <?php endif; ?>
      <div class="cap">
        <h2><?= e($p['title']) ?></h2>
        <?php if ($p['price']): ?><p class="price"><?= e($p['price']) ?></p><?php endif; ?>
        <?php if (!empty($L[$p['id']])): ?>
        <div class="shop">
          <button type="button" class="btn shopbtn" aria-expanded="false">Shop now ▾</button>
          <ul class="menu" hidden>
            <?php foreach ($L[$p['id']] as $l): ?>
            <li><a href="go.php?l=<?= (int)$l['id'] ?>" target="_blank" rel="noopener sponsored"><?= e($l['store']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
  </div>
  <p id="empty" class="empty" <?= $items ? 'hidden' : '' ?>>No products yet. Log in as admin to add the first one.</p>
</main>
<footer>© <?= date('Y') ?> <?= e(BRAND) ?>. Buying happens on the seller's website.</footer>
<script src="assets/app.js"></script>
</body>
</html>
