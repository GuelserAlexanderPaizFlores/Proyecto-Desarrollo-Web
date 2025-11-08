<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';
require_admin(); verify_csrf();

$id = (int)($_POST['id'] ?? 0);
$admin = current_user();

$pdo->beginTransaction();
try {
  $stmt = $pdo->prepare('SELECT * FROM requests WHERE id = :id FOR UPDATE');
  $stmt->execute([':id' => $id]);
  $r = $stmt->fetch();
  if (!$r || $r['status'] !== 'PENDING') throw new Exception('Solicitud inválida');
  $payload = json_decode($r['payload'], true) ?? [];

  switch ($r['type']) {
    case 'MEMBER_CREATE':
      $pdo->prepare("INSERT INTO members (code, first_name, last_name, date_of_birth, address, phone, email, is_baptized, join_date, notes)
                     VALUES (:code,:first_name,:last_name,:date_of_birth,:address,:phone,:email,:is_baptized,:join_date,:notes)")
          ->execute([
            ':code'=>$payload['code']??null, ':first_name'=>$payload['first_name']??null, ':last_name'=>$payload['last_name']??null,
            ':date_of_birth'=>$payload['date_of_birth']??null, ':address'=>$payload['address']??null, ':phone'=>$payload['phone']??null,
            ':email'=>$payload['email']??null, ':is_baptized'=>!empty($payload['is_baptized'])?1:0, ':join_date'=>$payload['join_date']??null,
            ':notes'=>$payload['notes']??null
          ]);
      $newId = $pdo->lastInsertId();
      if (!empty($payload['is_baptized']) && !empty($payload['baptism_date'])) {
        $pdo->prepare("INSERT INTO baptisms (member_id, baptism_date, notes) VALUES (:mid,:d,:n)")
            ->execute([':mid'=>$newId, ':d'=>$payload['baptism_date'], ':n'=>$payload['notes'] ?? null]);
      }
      break;

    case 'MEMBER_UPDATE':
      if (!$r['target_id']) throw new Exception('Falta target_id');
      $fields = ['code','first_name','last_name','date_of_birth','address','phone','email','is_baptized','join_date','notes'];
      $sets = []; $params = [':id'=>$r['target_id']];
      foreach ($fields as $f) {
        if (array_key_exists($f, $payload)) { $sets[] = "$f = :$f"; $params[":$f"] = $payload[$f]; }
      }
      if ($sets) {
        $sql = 'UPDATE members SET ' . implode(',', $sets) . ' WHERE id = :id';
        $pdo->prepare($sql)->execute($params);
      }
      if (array_key_exists('is_baptized', $payload)) {
        $isB = (int)$payload['is_baptized'] ? 1 : 0;
        if ($isB) {
          if (!empty($payload['baptism_date'])) {
            $pdo->prepare("INSERT INTO baptisms (member_id, baptism_date, notes) VALUES (:mid,:d,:n)
                           ON DUPLICATE KEY UPDATE baptism_date=VALUES(baptism_date), notes=VALUES(notes)")
                ->execute([':mid'=>$r['target_id'], ':d'=>$payload['baptism_date'], ':n'=>$payload['notes'] ?? null]);
          }
        } else {
          $pdo->prepare('DELETE FROM baptisms WHERE member_id = :mid')->execute([':mid'=>$r['target_id']]);
        }
      }
      break;

    case 'MEMBER_DELETE':
      if (!$r['target_id']) throw new Exception('Falta target_id');
      $pdo->prepare('DELETE FROM members WHERE id = :id')->execute([':id'=>$r['target_id']]);
      break;

    case 'BAPTISM_CREATE':
      $mid = (int)($payload['member_id'] ?? 0);
      if (!$mid) throw new Exception('member_id requerido');
      $pdo->prepare("INSERT INTO baptisms (member_id, baptism_date, notes) VALUES (:mid,:d,:n)")
          ->execute([':mid'=>$mid, ':d'=>$payload['baptism_date'] ?? null, ':n'=>$payload['notes'] ?? null]);
      $pdo->prepare("UPDATE members SET is_baptized = 1 WHERE id = :mid")->execute([':mid'=>$mid]);
      break;

    case 'BAPTISM_UPDATE':
      if (!$r['target_id']) throw new Exception('Falta target_id');
      $sets = []; $params = [':id'=>$r['target_id']];
      foreach (['baptism_date','notes'] as $f) {
        if (array_key_exists($f, $payload)) { $sets[] = "$f = :$f"; $params[":$f"] = $payload[$f]; }
      }
      if ($sets) {
        $sql = 'UPDATE baptisms SET ' . implode(',', $sets) . ' WHERE id = :id';
        $pdo->prepare($sql)->execute($params);
      }
      break;

    case 'BAPTISM_DELETE':
      if (!$r['target_id']) throw new Exception('Falta target_id');
      $x = $pdo->prepare('SELECT member_id FROM baptisms WHERE id = :id');
      $x->execute([':id'=>$r['target_id']]); $row = $x->fetch();
      if ($row) {
        $pdo->prepare('DELETE FROM baptisms WHERE id = :id')->execute([':id'=>$r['target_id']]);
        $pdo->prepare('UPDATE members SET is_baptized = 0 WHERE id = :mid')->execute([':mid'=>$row['member_id']]);
      }
      break;

    default: throw new Exception('Tipo de solicitud no implementado');
  }

  $pdo->prepare("UPDATE requests SET status='APPROVED', reviewed_by=:rb, decision_at=NOW() WHERE id=:id")
      ->execute([':rb'=>$admin['id'], ':id'=>$id]);

  $pdo->commit();
  header('Location: ' . url('/requests/index.php')); exit;
} catch (Throwable $e) {
  $pdo->rollBack(); http_response_code(500); echo 'Error: ' . $e->getMessage();
}
