<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin(); verify_csrf();
$id = (int)($_POST['id'] ?? 0); $reason = trim($_POST['reason'] ?? ''); $admin = current_user();
$pdo->prepare("UPDATE requests SET status='REJECTED', reason=:r, reviewed_by=:rb, decision_at=NOW() WHERE id=:id AND status='PENDING'")->execute([':r'=>$reason, ':rb'=>$admin['id'], ':id'=>$id]);
header('Location: ' . url('/requests/index.php')); exit;
