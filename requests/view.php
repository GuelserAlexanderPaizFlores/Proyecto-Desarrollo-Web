<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/access.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT r.*, u.full_name FROM requests r JOIN users u ON u.id = r.submitted_by WHERE r.id = :id');
$stmt->execute([':id'=>$id]); $r = $stmt->fetch();
if (!$r) { http_response_code(404); echo 'Solicitud no encontrada'; exit; }
$payload = json_decode($r['payload'], true) ?? [];

render_head('Solicitud #'.$r['id'], ['/assets/css/requests_view.css']); render_navbar();
?>
<div class="container">
  <h2 class="mb-3">Solicitud #<?=$r['id']?> — <?=h($r['type'])?></h2>
  <div class="card mb-3">
    <p><strong>Tabla destino:</strong> <?=h($r['target_table'])?> <?php if ($r['target_id']) echo '#'.(int)$r['target_id']; ?></p>
    <p><strong>Enviada por:</strong> <?=h($r['full_name'])?> | <strong>Fecha:</strong> <?=h($r['created_at'])?></p>
    <pre><?=h(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))?></pre>
    <form method="post" action="<?=h(url('/requests/approve.php'))?>" style="display:inline-block;">
      <?=csrf_field()?><input type="hidden" name="id" value="<?=$r['id']?>">
      <button class="btn primary">Aprobar & aplicar</button>
    </form>
    <form method="post" action="<?=h(url('/requests/reject.php'))?>" style="display:inline-block; margin-left:8px;">
      <?=csrf_field()?><input type="hidden" name="id" value="<?=$r['id']?>">
      <input type="text" name="reason" placeholder="Motivo de rechazo" style="padding:8px; border:1px solid #d1d5db; border-radius:8px; width:260px;">
      <button class="btn" style="margin-left:8px;">Rechazar</button>
    </form>
  </div>
</div>
<?php render_footer(); ?>
