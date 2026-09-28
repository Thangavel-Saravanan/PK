<?php require '../config.php'; require '../helpers.php';
if (!is_admin()) { header('Location: login.php'); exit; }
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
  if (isset($_POST['delete'])) {
    $id = (int)$_POST['delete'];
    $st = db()->prepare('SELECT video,image_url FROM products WHERE id=?'); $st->execute([$id]); $r = $st->fetch() ?: [];
    drop_video((string)($r['video'] ?? '')); drop_video((string)($r['image_url'] ?? ''));
    db()->prepare('DELETE FROM product_links WHERE product_id=?')->execute([$id]);
    db()->prepare('DELETE FROM products WHERE id=?')->execute([$id]); $msg = 'Product deleted.';
  } else {
    $t = trim($_POST['title'] ?? ''); $pr = trim($_POST['price'] ?? '');
    $cat = mb_substr(trim($_POST['category'] ?? ''), 0, 60); $vl = trim($_POST['video_link'] ?? ''); $img = trim($_POST['image_url'] ?? '');
    [$vp, $err] = save_video('video_file'); [$ip, $ierr] = save_image('image_file');
    $upV = $vp; $upI = $ip; $done = false;
    if ($vp === '' && $vl !== '' && valid_url($vl)) $vp = $vl;
    [$links, $lerr] = parse_links();
    if ($err || $ierr) $msg = $err ?: $ierr;
    elseif ($t === '') $msg = 'Enter a title.';
    elseif ($lerr) $msg = $lerr;
    elseif ($vl !== '' && $upV === '' && !valid_url($vl)) $msg = 'Video link is not valid.';
    elseif ($vp === '' && $ip === '' && $img === '') $msg = 'Upload a video or an image, or paste a link.';
    elseif ($ip === '' && $img !== '' && !valid_url($img)) $msg = 'Image link is not valid.';
    else {
      $imgF = $ip !== '' ? $ip : $img;
      db()->prepare('INSERT INTO products (title,price,category,video,image_url,link,store) VALUES (?,?,?,?,?,?,?)')
        ->execute([$t, $pr ?: null, $cat, $vp, $imgF ?: null, $links[0][1], $links[0][0]]);
      save_links((int)db()->lastInsertId(), $links); $msg = 'Product added.'; $done = true;
    }
    if (!$done) { drop_video($upV); drop_video($upI); }
  }
}
$items = db()->query('SELECT p.*, (SELECT GROUP_CONCAT(store SEPARATOR ", ") FROM product_links l WHERE l.product_id=p.id) plats FROM products p ORDER BY p.id DESC')->fetchAll();
require '_head.php'; ?>
<main class="dash">
  <form class="panel" method="post" enctype="multipart/form-data">
    <h1>Add a product</h1>
    <?php if ($msg): ?><p class="note"><?= e($msg) ?></p><?php endif; ?>
    <input type="hidden" name="csrf" value="<?= csrf() ?>">
    <p class="hint">Add a video, an image, or a link. Any one is enough. If there is a video it plays; if not, the image shows.</p>
    <label>Title<input name="title" maxlength="150" required></label>
    <label>Price (optional)<input name="price" maxlength="30" placeholder="₹999"></label>
    <?= cat_input() ?>
    <label>Video file (.mp4, .webm), optional<input name="video_file" type="file" accept="video/mp4,video/webm"></label>
    <label>Or direct video link (.mp4), optional<input name="video_link" type="url" placeholder="https://…/clip.mp4"></label>
    <label>Image file (JPG, PNG, WEBP, GIF…), optional<input name="image_file" type="file" accept="image/*"></label>
    <label>Or image link, optional<input name="image_url" type="url" placeholder="https://…/photo.jpg"></label>
    <strong>Shopping platforms</strong>
    <?= link_rows([]) ?>
    <button class="btn">Add product</button>
  </form>
  <section>
    <h1>Products (<?= count($items) ?>)</h1>
    <?php if (!$items): ?><p class="empty">Nothing here yet. Add your first product.</p><?php endif; ?>
    <?php foreach ($items as $p): ?>
    <form class="row" method="post">
      <?php if ($p['video']): ?><video src="<?= e(media($p['video'], '../')) ?>" muted preload="metadata"></video>
      <?php else: ?><img src="<?= e(media((string)$p['image_url'], '../')) ?>" alt=""><?php endif; ?>
      <div><strong><?= e($p['title']) ?></strong><br><small><?= ($p['category'] ?? '') !== '' ? e($p['category']).' · ' : '' ?><?= e($p['plats']) ?> · <?= (int)$p['clicks'] ?> clicks</small></div>
      <input type="hidden" name="csrf" value="<?= csrf() ?>">
      <a class="btn" href="edit.php?id=<?= (int)$p['id'] ?>">Edit</a>
      <button class="btn danger" name="delete" value="<?= (int)$p['id'] ?>" onclick="return confirm('Delete this product?')">Delete</button>
    </form>
    <?php endforeach; ?>
  </section>
</main>
<script src="../assets/app.js"></script></body></html>
