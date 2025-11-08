<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$u = current_user();

if ($u['role'] === 'ADMIN') {
  $pdo->beginTransaction();
  try {
    $stmt = $pdo->prepare("SELECT member_id FROM baptisms WHERE id = :id FOR UPDATE");
    $stmt->execute([':id'=>$id]);
    $row = $stmt->fetch();
    if ($row) {
      $pdo->prepare("DELETE FROM baptisms WHERE id = :id")->execute([':id'=>$id]);
      $pdo->prepare("UPDATE members SET is_baptized = 0 WHERE id = :mid")->execute([':mid'=>$row['member_id']]);
    }
    $pdo->commit();
    header('Location: ' . url('/baptisms/index.php')); exit;
  } catch (Throwable $e) {
    $pdo->rollBack(); http_response_code(500); echo 'Error: '.$e->getMessage();
  }
} else {
  $pdo->prepare("INSERT INTO requests (type, target_table, target_id, payload, submitted_by) VALUES ('BAPTISM_DELETE','baptisms',:tid,'{}',:uid)")
      ->execute([':tid'=>$id, ':uid'=>$u['id']]);
  header('Location: ' . url('/requests/index.php?sent=1')); exit;
}
