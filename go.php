<?php
require 'config.php';
$id = (int)($_GET['l'] ?? 0);
$st = db()->prepare('SELECT url, product_id FROM product_links WHERE id=?'); $st->execute([$id]);
$row = $st->fetch();
if (!$row || !valid_url($row['url'])) { http_response_code(404); exit('Link not found'); }
db()->prepare('UPDATE product_links SET clicks=clicks+1 WHERE id=?')->execute([$id]);
db()->prepare('UPDATE products SET clicks=clicks+1 WHERE id=?')->execute([$row['product_id']]);
header('Location: '.$row['url'], true, 302);
