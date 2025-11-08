<?php
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/db.php';
require_login();

// Paginación
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset   = ($page - 1) * $per_page;
$total    = (int)$pdo->query("SELECT COUNT(*) AS c FROM members")->fetch()['c'];
$pages    = max(1, (int)ceil($total / $per_page));

// Listado ASC por código
$stmt = $pdo->prepare("
  SELECT id, code, first_name, last_name, date_of_birth, address, phone, email, is_baptized, join_date, notes
  FROM members
  ORDER BY CAST(code AS UNSIGNED) ASC, code ASC
  LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

render_head('Miembros', ['/assets/css/members_index.css']);
render_navbar();
?>
<div class="members-page">
  <div class="container">
    <div class="members-title"><h2>👥 Miembros</h2></div>

    <div class="actions-top">
      <a href="<?=h(url('/members/create.php'))?>" class="btn glass glass-green">Nuevo miembro</a>
    </div>

    <div class="card fixed-box">
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Código</th>
              <th>Nombres</th>
              <th>Apellidos</th>
              <th>Edad</th>
              <th>Dirección</th>
              <th>Teléfono</th>
              <th>Correo</th>
              <th>Estado</th>
              <th>Ingreso</th>
              <th>Notas</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): $age = function_exists('calc_age') ? calc_age($r['date_of_birth']) : null; ?>
            <tr>
              <td><?=h($r['code'])?></td>
              <td><?=h($r['first_name'])?></td>
              <td><?=h($r['last_name'])?></td>
              <td><?= $age !== null ? $age : '—' ?></td>
              <td><span class="has-popover" data-popover="<?=h($r['address'])?>"><?=h($r['address'])?></span></td>
              <td><?=h($r['phone'])?></td>
              <td><span class="has-popover" data-popover="<?=h($r['email'])?>"><?=h($r['email'])?></span></td>
              <td><?= $r['is_baptized'] ? 'Bautizado' : 'No bautizado' ?></td>
              <td><?=h($r['join_date'])?></td>
              <td>
                <button type="button"
                        class="btn small glass glass-notes btn-notes"
                        data-note="<?=h($r['notes'])?>">Notas</button>
              </td>
              <td class="row-actions">
                <a class="btn small glass glass-orange" href="<?=h(url('/members/edit.php?id='.$r['id']))?>">Editar</a>
                <a class="btn small glass glass-red"    href="<?=h(url('/members/delete.php?id='.$r['id']))?>" onclick="return confirm('¿Eliminar?');">Eliminar</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Paginación (fuera del box) -->
    <div class="pagination">
      <?php
        $base = url('/members/index.php');
        $first = 1; $last = $pages;
        $prev = max(1, $page - 1);
        $next = min($pages, $page + 1);
      ?>
      <a class="page-btn <?= $page==1?'disabled':'' ?>" href="<?=h($base.'?page='.$first)?>">« Inicio</a>
      <a class="page-btn <?= $page==1?'disabled':'' ?>" href="<?=h($base.'?page='.$prev)?>">‹ Atrás</a>
      <span class="page-info">Página <?= $page ?> de <?= $pages ?> — mostrando <?= $total ? ($offset+1) : 0 ?>–<?= min($offset + $per_page, $total) ?> de <?= $total ?></span>
      <a class="page-btn <?= $page==$pages?'disabled':'' ?>" href="<?=h($base.'?page='.$next)?>">Siguiente ›</a>
      <a class="page-btn <?= $page==$pages?'disabled':'' ?>" href="<?=h($base.'?page='.$last)?>">Fin »</a>
    </div>

    <!-- Modal Notas (dentro del contenedor para que le afecte el CSS) -->
    <div id="notes-backdrop" class="modal-backdrop" aria-hidden="true">
      <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="notes-title">
        <div class="modal-header">
          <h3 id="notes-title">Notas del miembro</h3>
          <button type="button" class="modal-close" aria-label="Cerrar">×</button>
        </div>
        <div class="modal-body">
          <pre class="modal-pre" id="notes-content"></pre>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
(function(){
  var backdrop = document.getElementById('notes-backdrop');
  var content  = document.getElementById('notes-content');
  if(!backdrop || !content) return;

  var btnClose = backdrop.querySelector('.modal-close');

  function openModal(text){
    content.textContent = text || '';
    backdrop.classList.add('show');
    backdrop.setAttribute('aria-hidden', 'false');
  }
  function closeModal(){
    backdrop.classList.remove('show');
    backdrop.setAttribute('aria-hidden', 'true');
    content.textContent = '';
  }

  document.addEventListener('click', function(ev){
    var btn = ev.target.closest ? ev.target.closest('.btn-notes') : null;
    if(btn){
      ev.preventDefault();
      openModal(btn.getAttribute('data-note') || '');
      return;
    }
    if(ev.target === backdrop || ev.target === btnClose){
      closeModal();
    }
  });

  document.addEventListener('keydown', function(ev){
    if(ev.key === 'Escape' && backdrop.classList.contains('show')) closeModal();
  });
})();
</script>

<?php render_footer(); ?>
