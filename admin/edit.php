<?php require '../config.php'; require '../helpers.php';
if (!is_admin()) { header('Location: login.php'); exit; }
$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT * FROM products WHERE id=?'); $st->execute([$id]); $p = $st->fetch();
if (!$p) { header('Location: dashboard.php'); exit; }
$ls = db()->prepare('SELECT store, url FROM product_links WHERE product_id=? ORDER BY id'); $ls->execute([$id]);
$rows = array_map('array_values', $ls->fetchAll());
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
  $t = trim($_POST['title'] ?? ''); $pr = trim($_POST['price'] ?? '');
  $cat = mb_substr(trim($_POST['category'] ?? ''), 0, 60); $vl = trim($_POST['video_link'] ?? ''); $img = trim($_POST['image_url'] ?? '');
  [$new, $err] = save_video('video_file'); [$ni, $ierr] = save_image('image_file'); [$links, $lerr] = parse_links();
  $curImg = (string)$p['image_url'];
  if ($err || $ierr) $msg = $err ?: $ierr;
  elseif ($t === '') $msg = 'Enter a title.';
  elseif ($lerr) $msg = $lerr;
  elseif ($ni === '' && $img !== '' && $img !== $curImg && !valid_url($img)) $msg = 'Image link is not valid.';
  elseif ($new === '' && $vl !== '' && $vl !== $p['video'] && !valid_url($vl)) $msg = 'Video link is not valid.';
  else {
    $vp = $p['video']; $imgF = $curImg;
    if ($new !== '') $vp = $new; elseif ($vl !== '' && $vl !== $p['video']) $vp = $vl;
    if ($ni !== '') $imgF = $ni; elseif ($img !== '' && $img !== $curImg) $imgF = $img; elseif ($img === '' && is_remote($curImg)) $imgF = '';
    if ($vp === '' && $imgF === '') $msg = 'Keep at least a video or an image.';
    else {
      if ($vp !== $p['video']) drop_video((string)$p['video']);
      if ($imgF !== $curImg) drop_video($curImg);
      db()->prepare('UPDATE products SET title=?,price=?,category=?,video=?,image_url=?,link=?,store=? WHERE id=?')
        ->execute([$t, $pr ?: null, $cat, $vp, $imgF ?: null, $links[0][1], $links[0][0], $id]);
      save_links($id, $links);
      header('Location: dashboard.php'); exit;
    }
  }
  drop_video($new); drop_video($ni);
  $p = array_merge($p, ['title' => $t, 'price' => $pr, 'category' => $cat, 'image_url' => $img !== '' ? $img : $curImg]);
  if (!$lerr) $rows = $links;
}
require '_head.php'; ?>
<main class="center">
  <form class="panel" method="post" enctype="multipart/form-data">
    <h1>Edit product</h1>
    <?php if ($msg): ?><p class="note"><?= e($msg) ?></p><?php endif; ?>
    <input type="hidden" name="csrf" value="<?= csrf() ?>">
    <?php if ($p['video']): ?><video class="prev" src="<?= e(media($p['video'], '../')) ?>" controls muted playsinline preload="metadata"></video><?php endif; ?>
    <?php if (!$p['video'] && $p['image_url']): ?><img class="prev" src="<?= e(media((string)$p['image_url'], '../')) ?>" alt=""><?php endif; ?>
    <p class="hint">Add a video, an image, or a link. Any one is enough. If there is a video it plays; if not, the image shows.</p>
    <label>Title<input name="title" maxlength="150" required value="<?= e($p['title']) ?>"></label>
    <label>Price (optional)<input name="price" maxlength="30" value="<?= e($p['price']) ?>"></label>
    <?= cat_input((string)($p['category'] ?? '')) ?>
    <label>Replace video file, optional (leave empty to keep current)<input name="video_file" type="file" accept="video/mp4,video/webm"></label>
    <label>Or direct video link (.mp4), optional<input name="video_link" type="url" value="<?= e(is_remote($p['video']) ? $p['video'] : '') ?>"></label>
    <label>Replace image file (JPG, PNG, WEBP, GIF…; leave empty to keep current)<input name="image_file" type="file" accept="image/*"></label>
    <label>Or image link, optional<input name="image_url" type="url" value="<?= e(is_remote((string)$p['image_url']) ? $p['image_url'] : '') ?>"></label>
    <strong>Shopping platforms</strong>
    <?= link_rows($rows) ?>
    <button class="btn">Save changes</button>
    <a class="link" href="dashboard.php">Cancel</a>
  </form>
</main>
<script src="../assets/app.js"></script></body></html>
