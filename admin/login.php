<?php require '../config.php'; require '../helpers.php';
if (is_admin()) { header('Location: dashboard.php'); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (csrf_ok() && admin_check((string)($_POST['user'] ?? ''), (string)($_POST['pass'] ?? ''))) {
    session_regenerate_id(true); $_SESSION['admin'] = true; header('Location: dashboard.php'); exit;
  }
  sleep(1); $err = 'Wrong username or password.';
} ?>
<!doctype html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin login</title>
<script>document.documentElement.dataset.theme=localStorage.getItem('theme')||(matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light')</script>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css"></head>
<body class="center">
<form class="panel" method="post">
  <h1>Admin login</h1>
  <?php if ($err): ?><p class="err"><?= e($err) ?></p><?php endif; ?>
  <input type="hidden" name="csrf" value="<?= csrf() ?>">
  <label>Username<input name="user" required autofocus autocomplete="username"></label>
  <label>Password<input name="pass" type="password" required autocomplete="current-password"></label>
  <button class="btn">Log in</button>
  <a class="link" href="../">Back to store</a>
</form>
<script src="../assets/app.js"></script></body></html>
