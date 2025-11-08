<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/access.php';

function render_head(string $title='Iglesia — Gestión', array $styles = []) {
  echo "<!doctype html>\n<html lang=\"es\">\n<head>\n<meta charset=\"utf-8\">\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n<title>".h($title)."</title>\n";
  echo '<link rel="stylesheet" href="'.h(url('/assets/css/global.css')).'">';
  echo '<link rel="stylesheet" href="'.h(url('/assets/css/navbar.css')).'">';
  foreach ($styles as $s) echo '<link rel="stylesheet" href="'.h(url($s)).'">';
  echo "</head><body>";
}

function is_active_href(string $href): bool {
  $cur = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
  $dest = parse_url($href, PHP_URL_PATH) ?: '';
  $destDir = rtrim(dirname($dest), '/');
  // Activo si es el mismo archivo, o si estamos dentro del subdirectorio (para edit/create en la misma sección)
  return $cur === $dest || ($destDir && strpos($cur, $destDir . '/') === 0);
}

function render_navbar() {
  if (!is_logged_in()) return; $u = current_user(); ?>
  <nav class="nav">
    <div class="nav-left">
      <a class="brand" href="<?=h(url('/public/dashboard.php'))?>">Iglesia</a>
      <?php if (nav_visible_for('Miembros')): ?>
        <?php $href = url('/members/index.php'); ?>
        <a class="<?= is_active_href($href) ? 'active' : '' ?>" href="<?=h($href)?>">Miembros</a>
      <?php endif; ?>
      <?php if (nav_visible_for('Bautizados')): ?>
        <?php $href = url('/baptisms/index.php'); ?>
        <a class="<?= is_active_href($href) ? 'active' : '' ?>" href="<?=h($href)?>">Bautizados</a>
      <?php endif; ?>
     
    </div>
    <div class="nav-right">
      <span><?=h($u['full_name'])?> (<?=h($u['role'])?>)</span>
      <a href="<?=h(url('/public/logout.php'))?>">Salir</a>
    </div>
  </nav>
<?php }
function render_footer() { echo "</body></html>"; }
