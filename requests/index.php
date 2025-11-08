<?php
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/db.php';
require_login();

/**
 * Listado de solicitudes (autorizaciones).
 * - Usa el mismo patrón de paginación (10 por página).
 * - Mantiene estilos iOS-glass via /assets/css/requests_index.css
 */

// Paginación
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset   = ($page - 1) * $per_page;

// Total
$cnt = $pdo->query("SELECT COUNT(*) AS c FROM requests");
$total = (int)($cnt->fetch()['c'] ?? 0);
$pages = max(1, (int)ceil($total / $per_page));

// Datos (orden más reciente primero)
$stmt = $pdo->prepare("SELECT * FROM requests ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

// Helper: extraer campos con nombres flexibles (por si tu esquema varía)
function req_col($r, $keys, $default='') {
  foreach ((array)$keys as $k) {
    if (isset($r[$k])) return $r[$k];
  }
  return $default;
}

render_head('Solicitudes', ['/assets/css/requests_index.css']);
render_navbar('requests');
?>
<div class="requests-page">
  <div class="container">
    <div class="page-title"><h2>🗂️ Solicitudes</h2></div>

    <div class="card fixed-box">
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Tipo</th>
              <th>Tabla/ID</th>
              <th>Enviada por</th>
              <th>Fecha</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <?php
              $id     = (int)req_col($r, ['id']);
              $type   = req_col($r, ['type','request_type','action'], '—');
              $table  = req_col($r, ['target_table','table','table_name','entity'], '—');
              $rid    = req_col($r, ['target_id','record_id','entity_id','ref_id'], '—');
              $who    = req_col($r, ['requested_by_name','requested_by_username','requested_by','user','username'], '—');
              $fecha  = req_col($r, ['created_at','created_on','ts'], '—');
              $estado = strtoupper((string)req_col($r, ['status','state','decision'], 'PENDIENTE'));
            ?>
            <tr>
              <td><?= h($id) ?></td>
              <td><?= h($type) ?></td>
              <td><?= h($table) ?>/<?= h($rid) ?></td>
              <td><?= h($who) ?></td>
              <td><?= h($fecha) ?></td>
              <td><?= h($estado) ?></td>
              <td class="row-actions">
                <a class="btn small glass glass-notes" href="<?= h(url('/requests/view.php?id='.$id)) ?>">Ver</a>
                <a class="btn small glass glass-green" href="<?= h(url('/requests/approve.php?id='.$id)) ?>">Aprobar</a>
                <a class="btn small glass glass-red" href="<?= h(url('/requests/reject.php?id='.$id)) ?>">Rechazar</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Paginación (igual a Miembros/Bautizados, fuera del box) -->
    <div class="pagination">
      <?php
        $base = url('/requests/index.php');
        $first = 1; $last = $pages;
        $prev = max(1, $page - 1);
        $next = min($pages, $page + 1);
      ?>
      <a class="page-btn <?= $page==1?'disabled':'' ?>" href="<?= h($base.'?page='.$first) ?>">« Inicio</a>
      <a class="page-btn <?= $page==1?'disabled':'' ?>" href="<?= h($base.'?page='.$prev) ?>">‹ Atrás</a>
      <span class="page-info">Página <?= $page ?> de <?= $pages ?> — mostrando
        <?= $total ? ($offset+1) : 0 ?>–<?= min($offset + $per_page, $total) ?> de <?= $total ?></span>
      <a class="page-btn <?= $page==$pages?'disabled':'' ?>" href="<?= h($base.'?page='.$next) ?>">Siguiente ›</a>
      <a class="page-btn <?= $page==$pages?'disabled':'' ?>" href="<?= h($base.'?page='.$last) ?>">Fin »</a>
    </div>
  </div>
</div>
<?php render_footer(); ?>
