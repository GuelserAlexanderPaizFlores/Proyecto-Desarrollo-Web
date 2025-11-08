<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8').'">'; }
function verify_csrf(): void { $ok = isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']); if (!$ok) { http_response_code(400); echo 'Token CSRF inválido'; exit; } }
