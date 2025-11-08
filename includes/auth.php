<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function current_user() { return $_SESSION['user'] ?? null; }
function is_logged_in(): bool { return !!current_user(); }
function require_login(): void { if (!is_logged_in()) { header('Location: ' . url('/public/index.php')); exit; } }
function has_role(string $role): bool { $u = current_user(); return $u && ($u['role'] === $role); }
function require_role(string $role): void { require_login(); if (!has_role($role)) { http_response_code(403); echo 'Acceso denegado'; exit; } }
function login(PDO $pdo, string $username, string $password): bool {
  $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :u AND is_active = 1 LIMIT 1');
  $stmt->execute([':u' => $username]); $user = $stmt->fetch();
  if ($user && ($password === $user['password'])) {
    $_SESSION['user'] = ['id'=>(int)$user['id'],'username'=>$user['username'],'full_name'=>$user['full_name'],'email'=>$user['email'],'role'=>$user['role']];
    return true;
  }
  return false;
}
function logout(): void {
  $_SESSION = []; if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params(); setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
  } session_destroy();
}
