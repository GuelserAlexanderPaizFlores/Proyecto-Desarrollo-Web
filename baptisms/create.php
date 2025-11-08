<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/helpers.php';
require_login();

$u = current_user();
$errors = [];
$members = $pdo->query("SELECT id, code, first_name, last_name FROM members WHERE is_baptized = 0 ORDER BY first_name, last_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $member_id = (int)($_POST['member_id'] ?? 0);
  $baptism_date = ($_POST['baptism_date'] ?? '') ?: null;
  $notes = trim($_POST['notes'] ?? '');
  if (!$member_id) $errors[] = 'Selecciona un miembro';
  if (!$baptism_date) $errors[] = 'La fecha de bautizo es obligatoria';

  if (!$errors) {
    if ($u['role'] === 'ADMIN') {
      $pdo->beginTransaction();
      try {
        $pdo->prepare("INSERT INTO baptisms (member_id, baptism_date, notes) VALUES (:mid,:d,:n)")
            ->execute([':mid'=>$member_id, ':d'=>$baptism_date, ':n'=>$notes]);
        $pdo->prepare("UPDATE members SET is_baptized=1 WHERE id=:id")->execute([':id'=>$member_id]);
        $pdo->commit();
        header('Location: ' . url('/baptisms/index.php')); exit;
      } catch (Throwable $e) { $pdo->rollBack(); $errors[] = 'Error al crear bautizo: '.$e->getMessage(); }
    } else {
      $payload = json_payload(['member_id'=>$member_id,'baptism_date'=>$baptism_date,'notes'=>$notes]);
      $pdo->prepare("INSERT INTO requests (type, target_table, payload, submitted_by) VALUES ('BAPTISM_CREATE','baptisms',:payload,:uid)")
          ->execute([':payload'=>$payload, ':uid'=>$u['id']]);
      header('Location: ' . url('/requests/index.php?sent=1')); exit;
    }
  }
}

render_head('Registrar bautizo', ['/assets/css/baptisms_create.css']); render_navbar();
?>
<div class="container">
  <h2 class="mb-3">Registrar bautizo</h2>
  <?php if ($errors): ?><div class="alert mb-3"><ul style="margin:0;padding-left:16px;"><?php foreach($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div><?php endif; ?>
  <form method="post" class="card">
    <?=csrf_field()?>
    <div class="form-grid">
      <div class="span-6">
        <label>Miembro*</label>
        <select name="member_id" required>
          <option value="">-- Selecciona --</option>
          <?php foreach ($members as $m): ?>
            <option value="<?=$m['id']?>"><?=h($m['code'].' — '.$m['first_name'].' '.$m['last_name'])?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="span-3"><label>Fecha de bautizo*</label><input type="date" name="baptism_date" required></div>
      <div class="span-12"><label>Notas</label><textarea name="notes" rows="3"></textarea></div>
    </div>
    <div>
      <button class="btn primary">Guardar</button>
      <a href="<?=h(url('/baptisms/index.php'))?>" class="btn">Cancelar</a>
    </div>
  </form>
</div>
<?php render_footer(); ?>
