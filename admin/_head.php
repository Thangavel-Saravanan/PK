<!doctype html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin</title>
<script>document.documentElement.dataset.theme=localStorage.getItem("theme")||(matchMedia("(prefers-color-scheme:dark)").matches?"dark":"light")</script>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css"></head>
<body>
<header class="bar"><a class="brand" href="../"><?= e(BRAND) ?></a><span class="grow"></span>
  <button id="theme" class="icon" aria-label="Switch light or dark theme"></button>
  <a class="link" href="account.php">Account</a>
  <a class="link" href="logout.php">Log out</a></header>
