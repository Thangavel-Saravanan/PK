<?php require '../config.php'; require '../helpers.php';
if (!is_admin()) { header('Location: login.php'); exit; }
$msg = ''; $ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
  $cur = (string)($_POST['current'] ?? ''); $nu = trim((string)($_POST['user'] ?? ''));
  $np = (string)($_POST['pass'] ?? ''); $np2 = (string)($_POST['pass2'] ?? '');
  if (!admin_check(admin_current_user(), $cur)) $msg = 'Current password is wrong.';
  elseif (mb_strlen($nu) < 3) $msg = 'Username must be at least 3 characters.';
  elseif ($np !== '' && strlen($np) < 8) $msg = 'New password must be at least 8 characters.';
  elseif ($np !== $np2) $msg = 'New passwords do not match.';
  else {
    $hash = password_hash($np !== '' ? $np : $cur, PASSWORD_DEFAULT);
    try {
      db()->prepare('INSERT INTO admin_account (id,username,password_hash) VALUES (1,?,?) ON DUPLICATE KEY UPDATE username=VALUES(username), password_hash=VALUES(password_hash)')->execute([$nu, $hash]);
      session_regenerate_id(true); $ok = true; $msg = 'Saved. Use the new details next time you log in.';
    } catch (Throwable $e) { $msg = 'Run migrate3.sql in phpMyAdmin first (admin_account table is missing).'; }
  }
}
require '_head.php'; ?>
<main class="center">
  <form class="panel" method="post" autocomplete="off">
    <h1>Admin account</h1>
    <?php if ($msg): ?><p class="<?= $ok ? 'note' : 'err' ?>"><?= e($msg) ?></p><?php endif; ?>
    <input type="hidden" name="csrf" value="<?= csrf() ?>">
    <label>Current password<input name="current" type="password" required autocomplete="current-password"></label>
    <label>Username<input name="user" required minlength="3" maxlength="60" value="<?= e(admin_current_user()) ?>" autocomplete="username"></label>
    <label>New password (leave empty to keep current)<input name="pass" type="password" minlength="8" autocomplete="new-password"></label>
    <label>Confirm new password<input name="pass2" type="password" minlength="8" autocomplete="new-password"></label>
    <button class="btn">Save changes</button>
    <a class="link" href="dashboard.php">Back to dashboard</a>
  </form>
</main>
<script src="../assets/app.js"></script></body></html>
