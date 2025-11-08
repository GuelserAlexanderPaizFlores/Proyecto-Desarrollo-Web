
<?php
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/db.php'; // <-- para tener $pdo

if (isset($_SESSION) && !empty($_SESSION['uid'])) {
  header('Location: '.url('/public/dashboard.php')); exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD']==='POST'){
  $u = $_POST['username'] ?? '';
  $p = $_POST['password'] ?? '';
  // FIX: auth.php espera (PDO $pdo, string $username, string $password)
  if(function_exists('login') && login($pdo, $u, $p)){
    header('Location: '.url('/public/dashboard.php')); exit;
  } else {
    $error = 'Usuario o contraseña incorrectos';
  }
}

render_head('Iniciar sesión', ['/assets/css/login.css']);
?>
<div class="login-shell">
  <!-- Logo tipo iglesia (SVG) -->
  <svg class="church-logo" viewBox="0 0 128 128" aria-hidden="true" role="img">
    <g fill="none" stroke="#16a34a" stroke-width="6" stroke-linecap="round" stroke-linejoin="round">
      <path d="M64 14 L112 86 H16 Z"/>
      <path d="M20 78 C38 70, 54 70, 64 78 C74 70, 90 70, 108 78" />
      <path d="M64 78 L64 108" />
      <path d="M22 86 C40 78, 56 78, 64 86 C72 78, 88 78, 106 86" />
    </g>
  </svg>
  <h1 class="login-title">Iniciar sesión</h1>
  <div class="signin-card">
    <?php if($error): ?><div class="error"><?=h($error)?></div><?php endif; ?>
    <form method="post" novalidate>
      <div class="form">
        <div class="input">
          <input name="username" placeholder="Usuario o correo" required autofocus>
          <!-- botón 'go' eliminado -->
        </div>
        <div class="input">
          <input name="password" type="password" placeholder="Contraseña" required>
        </div>
        <!-- bloque 'Mantener sesión iniciada' ya eliminado -->
        <button class="submit" type="submit">Iniciar sesión</button>
      </div>
    </form>
  </div>
  <div class="footer-links">
    <a href="#">¿Olvidaste tu contraseña?</a> · <a href="#">Políticas</a>
  </div>
</div>
<?php render_footer(); ?>
