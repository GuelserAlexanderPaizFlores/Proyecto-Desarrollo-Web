<?php
function h(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function calc_age(?string $date_of_birth): ?int {
  if (!$date_of_birth) return null;
  try { $dob = new DateTime($date_of_birth); $now = new DateTime('today'); return $dob->diff($now)->y; }
  catch (Exception $e) { return null; }
}
function json_payload(array $data): string {
  return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
function app_config(): array {
  static $cfg = null; if ($cfg === null) $cfg = require __DIR__ . '/../config/config.php'; return $cfg;
}
function url(string $path): string {
  $base = rtrim(app_config()['app']['base_url'] ?? '', '/');
  return $base . $path;
}
