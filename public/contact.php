<?php
// /public/contact.php
declare(strict_types=1);

// (Opcional) Si tienes config global y helpers:
$hasConfig = false;
$baseUrl = '/';
try {
    $cfg = __DIR__ . '/../config/config.php';
    if (is_file($cfg)) {
        require_once $cfg;
        $hasConfig = true;
        if (function_exists('base_url')) {
            $baseUrl = base_url('/');
        } elseif (defined('BASE_URL')) {
            $baseUrl = rtrim(BASE_URL, '/') . '/';
        }
    }
} catch (\Throwable $e) {
    // continuar sin config
}

// Escape helper por si no existe h()
if (!function_exists('h')) {
    function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "<!doctype html><meta charset='utf-8'><title>405</title><p>Método no permitido.</p>";
    exit;
}

// === Configuración de envío por correo (opcional) ===
// Cambia a true si tu hosting permite mail().
const ENABLE_MAIL = false;
const MAIL_TO     = 'info@tudominio.com'; // cambia por el correo de destino

// === Recibir y validar ===
$honeypot = $_POST['website'] ?? ''; // debe venir vacío
$nombre   = trim($_POST['nombre']  ?? '');
$email    = trim($_POST['email']   ?? '');
$asunto   = trim($_POST['asunto']  ?? '');
$mensaje  = trim($_POST['mensaje'] ?? '');

$errores = [];

if ($honeypot !== '') {
    // Bot/Spam
    $errores[] = 'Solicitud inválida.';
}
if ($nombre === '')  { $errores[] = 'El nombre es requerido.'; }
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El correo electrónico no es válido.';
}
if ($asunto === '')  { $errores[] = 'El asunto es requerido.'; }
if ($mensaje === '') { $errores[] = 'El mensaje es requerido.'; }

// (Opcional) CSRF: si tu proyecto ya usa un token, verifícalo aquí.
// if (!csrf_verify($_POST['csrf_token'] ?? '')) { $errores[] = 'Token CSRF inválido.'; }

if ($errores) {
    http_response_code(422);
    // Vista de error simple, estilo minimalista coherente
    ?>
    <!doctype html>
    <html lang="es">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title>Contacto — Error</title>
      <style>
        :root{
          --fg:#111827; --muted:#6b7280; --border:#e5e7eb; --err:#b91c1c;
          --glass: rgba(255,255,255,.8); --stroke: rgba(17,24,39,.14); --blur:saturate(180%) blur(14px);
        }
        *{box-sizing:border-box}
        body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;color:var(--fg);background:#fff}
        .wrap{min-height:100svh;display:grid;place-items:center;padding:20px}
        .card{max-width:700px;width:100%;background:var(--glass);backdrop-filter:var(--blur);-webkit-backdrop-filter:var(--blur);
              border:1px solid var(--stroke);border-radius:16px;padding:24px 20px}
        h1{margin:0 0 8px;font-size:clamp(22px,4vw,28px)}
        ul{margin:10px 0 0 18px}
        .err{color:var(--err);font-weight:700}
        .row{margin-top:18px;display:flex;gap:10px;flex-wrap:wrap}
        .btn{display:inline-block;padding:10px 14px;border-radius:12px;border:1px solid var(--border);text-decoration:none;color:var(--fg)}
        .btn.primary{background:#0ea5e9;color:#fff;border-color:transparent}
      </style>
    </head>
    <body>
      <main class="wrap">
        <section class="card" role="alert" aria-live="polite">
          <h1 class="err">No se pudo enviar el formulario</h1>
          <p>Revisa los siguientes puntos:</p>
          <ul>
            <?php foreach ($errores as $e): ?>
              <li><?= h($e) ?></li>
            <?php endforeach; ?>
          </ul>
          <div class="row">
            <a class="btn" href="<?= h($baseUrl) ?>#contacto">Volver al formulario</a>
            <a class="btn primary" href="<?= h($baseUrl) ?>">Ir al inicio</a>
          </div>
        </section>
      </main>
    </body>
    </html>
    <?php
    exit;
}

// === (Opcional) Enviar correo ===
$mailEnviado = false;
if (ENABLE_MAIL) {
    $subject = "[Contacto Iglesia] " . $asunto;
    $body    = "Nombre: {$nombre}\nEmail: {$email}\nAsunto: {$asunto}\n\nMensaje:\n{$mensaje}\n";
    $headers = "From: {$nombre} <{$email}>\r\nReply-To: {$email}\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    // Algunos hostings gratuitos bloquean mail(); si falla, la página de éxito igualmente se mostrará.
    $mailEnviado = @mail(MAIL_TO, $subject, $body, $headers);
}

// === Pantalla de éxito ===
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Contacto — ¡Recibimos tu mensaje!</title>
  <style>
    :root{
      --fg:#111827; --muted:#6b7280; --border:#e5e7eb;
      --glass: rgba(255,255,255,.85); --stroke: rgba(17,24,39,.14); --blur: saturate(180%) blur(14px);
      --ok:#10b981;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;color:var(--fg);background:#fff}
    .wrap{min-height:100svh;display:grid;place-items:center;padding:20px}
    .card{max-width:760px;width:100%;background:var(--glass);backdrop-filter:var(--blur);-webkit-backdrop-filter:var(--blur);
          border:1px solid var(--stroke);border-radius:16px;padding:24px 20px;box-shadow:0 10px 30px rgba(0,0,0,.06)}
    h1{margin:0 0 8px;font-size:clamp(22px,4.6vw,30px)}
    .ok{color:var(--ok);font-weight:800}
    .meta{color:var(--muted);margin:8px 0 16px}
    .row{display:flex;gap:10px;flex-wrap:wrap}
    .btn{display:inline-block;padding:10px 14px;border-radius:12px;border:1px solid var(--border);text-decoration:none;color:var(--fg)}
    .btn.primary{background:#0ea5e9;color:#fff;border-color:transparent}
    .field{margin:8px 0}
    .label{font-size:12px;color:var(--muted)}
    .value{font-weight:600}
  </style>
</head>
<body>
  <main class="wrap">
    <section class="card" role="status" aria-live="polite">
      <h1><span class="ok">¡Recibimos tu mensaje!</span></h1>
      <p class="meta">Gracias, <strong><?= h($nombre) ?></strong>. Nos pondremos en contacto muy pronto.</p>

      <div class="field"><div class="label">Asunto</div><div class="value"><?= h($asunto) ?></div></div>
      <div class="field"><div class="label">Correo</div><div class="value"><?= h($email) ?></div></div>
      <div class="field"><div class="label">Mensaje</div><div class="value"><?= nl2br(h($mensaje)) ?></div></div>

      <?php if (ENABLE_MAIL): ?>
        <p class="meta">
          Estado del envío por correo: <strong><?= $mailEnviado ? 'enviado' : 'no disponible (hosting)' ?></strong>
        </p>
      <?php endif; ?>

      <div class="row" style="margin-top:14px">
        <a class="btn" href="<?= h($baseUrl) ?>#contacto">Enviar otro mensaje</a>
        <a class="btn primary" href="<?= h($baseUrl) ?>">Volver al inicio</a>
      </div>
    </section>
  </main>
</body>
</html>
