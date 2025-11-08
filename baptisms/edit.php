<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/helpers.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT b.*, m.first_name, m.last_name, m.code FROM baptisms b JOIN members m ON m.id=b.member_id WHERE b.id=:id");
$stmt->execute([':id'=>$id]);
$b = $stmt->fetch();
if (!$b) { http_response_code(404); echo 'Registro no encontrado'; exit; }

$u = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $baptism_date = ($_POST['baptism_date'] ?? '') ?: null;
  $notes = trim($_POST['notes'] ?? '');
  if (!$baptism_date) $errors[] = 'La fecha de bautizo es obligatoria';

  if (!$errors) {
    if ($u['role'] === 'ADMIN') {
      $pdo->prepare("UPDATE baptisms SET baptism_date=:d, notes=:n WHERE id=:id")
          ->execute([':d'=>$baptism_date, ':n'=>$notes, ':id'=>$id]);
      header('Location: ' . url('/baptisms/index.php')); exit;
    } else {
      $payload = json_payload(['baptism_date'=>$baptism_date, 'notes'=>$notes]);
      $pdo->prepare("INSERT INTO requests (type, target_table, target_id, payload, submitted_by) VALUES ('BAPTISM_UPDATE','baptisms',:tid,:payload,:uid)")
          ->execute([':tid'=>$id, ':payload'=>$payload, ':uid'=>$u['id']]);
      header('Location: ' . url('/requests/index.php?sent=1')); exit;
    }
  }
}

render_head('Editar bautizo', ['/assets/css/baptisms_edit.css']); render_navbar();
?>
<div class="container">
  <h2 class="mb-3">Editar bautizo — <?=h($b['code'].' — '.$b['first_name'].' '.$b['last_name'])?></h2>
  <?php if ($errors): ?><div class="alert mb-3"><ul style="margin:0; padding-left:16px;"><?php foreach($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div><?php endif; ?>
  <form method="post" class="card">
    <?=csrf_field()?>
    <div class="form-grid">
      <div class="span-3"><label>Fecha de bautizo*</label><input type="date" name="baptism_date" value="<?=h($b['baptism_date'])?>" required></div>
      <div class="span-12"><label>Notas</label><textarea name="notes" rows="3"><?=h($b['notes'])?></textarea></div>
    </div>
    <div>
      <button class="btn primary">Guardar cambios</button>
      <a href="<?=h(url('/baptisms/index.php'))?>" class="btn">Cancelar</a>
    </div>
  </form>
</div>
<?php render_footer(); ?>
