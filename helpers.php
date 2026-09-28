<?php
// Video upload helpers (kept separate so your config.php stays untouched)
function save_video(string $field): array {   // returns [path, error]
  if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return ['', null];
  $f = $_FILES[$field];
  if ($f['error'] !== UPLOAD_ERR_OK) return ['', 'Upload failed. The video may be too big (raise upload_max_filesize and post_max_size in php.ini).'];
  $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, ['mp4', 'webm', 'm4v'], true)) return ['', 'Video must be .mp4, .webm or .m4v.'];
  $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
  if (strpos($mime, 'video/') !== 0) return ['', 'That file is not a video.'];
  $dir = __DIR__ . '/uploads';
  if (!is_dir($dir)) mkdir($dir, 0755, true);
  $name = bin2hex(random_bytes(8)) . '.' . $ext;
  if (!move_uploaded_file($f['tmp_name'], "$dir/$name")) return ['', 'Could not save the video. Check that the uploads folder is writable.'];
  return ["uploads/$name", null];
}
function is_remote(string $v): bool { return (bool)preg_match('#^https?://#i', $v); }
function drop_video(string $v): void { if ($v !== '' && !is_remote($v)) @unlink(__DIR__ . '/' . $v); }
function media(string $v, string $prefix = ''): string { return is_remote($v) ? $v : $prefix . $v; }

// ---- shopping platform links (many per product) ----
function parse_links(): array {   // returns [links, error]; links = [[store, url], ...]
  $out = []; $names = $_POST['plat'] ?? []; $urls = $_POST['url'] ?? [];
  foreach ((array)$urls as $i => $u) {
    $u = trim((string)$u); if ($u === '') continue;
    if (!valid_url($u)) return [[], 'One of the platform links is not valid (it must start with http).'];
    $n = trim((string)($names[$i] ?? '')); if ($n === '') $n = store_name($u);
    $out[] = [mb_substr($n, 0, 40), $u];
  }
  if (!$out) return [[], 'Add at least one shopping platform link.'];
  return [array_slice($out, 0, 8), null];
}
function save_links(int $pid, array $links): void {
  db()->prepare('DELETE FROM product_links WHERE product_id=?')->execute([$pid]);
  $st = db()->prepare('INSERT INTO product_links (product_id,store,url) VALUES (?,?,?)');
  foreach ($links as $l) $st->execute([$pid, $l[0], $l[1]]);
}
function link_rows(array $rows): string {
  if (!$rows) $rows = [['', '']];
  $h = '<datalist id="stores"><option value="Amazon"><option value="Flipkart"><option value="Myntra"><option value="Ajio"><option value="Meesho"></datalist><div id="links">';
  foreach (array_values($rows) as $i => $r)
    $h .= '<div class="lrow"><input name="plat[]" list="stores" maxlength="40" placeholder="Platform (e.g. Amazon)" value="'.e($r[0]).'">'
        . '<input name="url[]" type="url" placeholder="https://…" value="'.e($r[1]).'"'.($i === 0 ? ' required' : '').'>'
        . '<button type="button" class="x" aria-label="Remove platform">✕</button></div>';
  return $h.'</div><button type="button" id="addlink" class="link">+ Add another platform</button>';
}

// ---- admin account: username + password hash live in the database (table admin_account) ----
// config.php ADMIN_USER / ADMIN_PASS are used only ONCE, to create the first admin row.
function admin_row() {
  static $row = null;
  if ($row !== null) return $row;
  try {
    db()->exec('CREATE TABLE IF NOT EXISTS admin_account (id TINYINT PRIMARY KEY, username VARCHAR(60) NOT NULL, password_hash VARCHAR(255) NOT NULL)');
    $r = db()->query('SELECT username, password_hash FROM admin_account WHERE id=1')->fetch();
    if (!$r) {
      $r = ['username' => ADMIN_USER, 'password_hash' => password_hash(ADMIN_PASS, PASSWORD_DEFAULT)];
      db()->prepare('INSERT IGNORE INTO admin_account (id,username,password_hash) VALUES (1,?,?)')->execute([$r['username'], $r['password_hash']]);
    }
    return $row = $r;
  } catch (Throwable $e) { return $row = false; }
}
function admin_check(string $user, string $pass): bool {
  $r = admin_row();
  return $r && hash_equals($r['username'], $user) && password_verify($pass, $r['password_hash']);
}
function admin_current_user(): string { $r = admin_row(); return $r ? $r['username'] : ADMIN_USER; }

// ---- categories ----
function categories(): array {
  try { return db()->query("SELECT DISTINCT category FROM products WHERE category<>'' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN); }
  catch (Throwable $e) { return []; }
}
function cat_input(string $v = ''): string {
  $h = '<datalist id="cats">';
  foreach (categories() as $c) $h .= '<option value="'.e($c).'">';
  return '<label>Category (e.g. Shirts, Shoes, Watches)<input name="category" list="cats" maxlength="60" value="'.e($v).'">'.$h.'</datalist></label>';
}

// ---- image upload (JPG, PNG, WEBP, GIF, AVIF, BMP) ----
function save_image(string $field): array {   // returns [path, error]
  if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return ['', null];
  $f = $_FILES[$field];
  if ($f['error'] !== UPLOAD_ERR_OK) return ['', 'Image upload failed. The file may be too big (raise upload_max_filesize and post_max_size in php.ini).'];
  $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'avif', 'bmp'], true)) return ['', 'Image must be JPG, PNG, WEBP, GIF, AVIF or BMP.'];
  $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
  if (strpos($mime, 'image/') !== 0 || $mime === 'image/svg+xml') return ['', 'That file is not an image.'];
  $dir = __DIR__ . '/uploads';
  if (!is_dir($dir)) mkdir($dir, 0755, true);
  $name = bin2hex(random_bytes(8)) . '.' . $ext;
  if (!move_uploaded_file($f['tmp_name'], "$dir/$name")) return ['', 'Could not save the image. Check that the uploads folder is writable.'];
  return ["uploads/$name", null];
}
