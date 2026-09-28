<?php
session_start();
// ---- CHANGE THESE ----
const DB_HOST = 'localhost', DB_NAME = 'praveenkrishmoo', DB_USER = 'root', DB_PASS = '';
const ADMIN_USER = 'admin', ADMIN_PASS = 'ChangeMe@123';
const BRAND = 'Praveenkrishmoo';
// ----------------------
function db(): PDO {
  static $pdo;
  return $pdo ??= new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
}
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrf_ok(): bool { return hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? ''); }
function is_admin(): bool { return !empty($_SESSION['admin']); }
function valid_url($u): bool { return filter_var($u, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $u); }
function store_name(string $url): string {
  $h = preg_replace('/^www\./', '', strtolower(parse_url($url, PHP_URL_HOST) ?? ''));
  $known = ['amazon' => 'Amazon', 'flipkart' => 'Flipkart', 'myntra' => 'Myntra', 'ajio' => 'Ajio', 'meesho' => 'Meesho'];
  foreach ($known as $k => $v) if (str_contains($h, $k)) return $v;
  return ucfirst(explode('.', $h)[0] ?: 'Store');
}
