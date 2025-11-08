<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/db.php';

require_login();

function next_member_code(PDO $pdo){
  $row = $pdo->query("SELECT COALESCE(MAX(CAST(code AS UNSIGNED)), 0) + 1 AS next_code FROM members")->fetch();
  $next = $row && isset($row['next_code']) ? (int)$row['next_code'] : 1;
  return (string)$next;
}

$errors = [];
$code = next_member_code($pdo);
$first_name = $last_name = $date_of_birth = $address = $phone = $email = $join_date = $notes = "";
$is_baptized = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $code         = trim($_POST['code'] ?? next_member_code($pdo));
  $first_name   = trim($_POST['first_name'] ?? '');
  $last_name    = trim($_POST['last_name'] ?? '');
  $date_of_birth= trim($_POST['date_of_birth'] ?? '');
  $address      = trim($_POST['address'] ?? '');
  $phone        = trim($_POST['phone'] ?? '');
  $email        = trim($_POST['email'] ?? '');
  $is_baptized  = isset($_POST['is_baptized']) ? (int)$_POST['is_baptized'] : 0;
  $join_date    = trim($_POST['join_date'] ?? '');
  $notes        = trim($_POST['notes'] ?? '');

  if ($first_name === '') $errors[] = "Nombres es requerido.";
  if ($last_name === '')  $errors[] = "Apellidos es requerido.";
  if ($join_date === '')  $errors[] = "Fecha de ingreso es requerida.";

  if (!$errors) {
    try {
      $stmt = $pdo->prepare("
        INSERT INTO members
          (code, first_name, last_name, date_of_birth, address, phone, email, is_baptized, join_date, notes)
        VALUES
          (:code, :first_name, :last_name, :date_of_birth, :address, :phone, :email, :is_baptized, :join_date, :notes)
      ");
      $stmt->execute([
        ':code'         => $code,
        ':first_name'   => $first_name,
        ':last_name'    => $last_name,
        ':date_of_birth'=> $date_of_birth ?: null,
        ':address'      => $address,
        ':phone'        => $phone,
        ':email'        => $email,
        ':is_baptized'  => $is_baptized,
        ':join_date'    => $join_date ?: null,
        ':notes'        => $notes,
      ]);
      header('Location: ' . url('/members/index.php') . '?created=1');
      exit;
    } catch (Throwable $e) {
      $errors[] = "No se pudo guardar el miembro. " . $e->getMessage();
      $code = next_member_code($pdo);
    }
  }
}

render_head('Nuevo miembro', ['/assets/css/members_create.css']);
render_navbar();
?>
<div class="container">
  <div class="card create-card">
    <h1 class="title">👤 Nuevo miembro</h1>

    <?php if ($errors): ?>
      <div class="alert">
        <?php foreach ($errors as $e): ?>
          <div><?= h($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" class="form">
      <div class="grid">
        <div class="col">
          <label>Código</label>
          <input name="code" value="<?= h($code) ?>" readonly>
        </div>
        <div class="col">
          <label>Nombres</label>
          <input name="first_name" value="<?= h($first_name) ?>" required>
        </div>
        <div class="col">
          <label>Apellidos</label>
          <input name="last_name" value="<?= h($last_name) ?>" required>
        </div>
        <div class="col">
          <label>Fecha de nacimiento</label>
          <input type="date" name="date_of_birth" value="<?= h($date_of_birth) ?>">
        </div>
        <div class="col">
          <label>Dirección</label>
          <input name="address" value="<?= h($address) ?>">
        </div>
        <div class="col">
          <label>Teléfono</label>
          <input name="phone" value="<?= h($phone) ?>">
        </div>
        <div class="col">
          <label>Correo</label>
          <input type="email" name="email" value="<?= h($email) ?>">
        </div>
        <div class="col">
          <label>Estado</label>
          <select name="is_baptized">
            <option value="0" <?= $is_baptized ? '' : 'selected' ?>>No bautizado</option>
            <option value="1" <?= $is_baptized ? 'selected' : '' ?>>Bautizado</option>
          </select>
        </div>
        <div class="col">
          <label>Fecha ingreso iglesia</label>
          <input type="date" name="join_date" value="<?= h($join_date) ?>" required>
        </div>
        <div class="col col-12">
          <label>Notas</label>
          <textarea name="notes" rows="4"><?= h($notes) ?></textarea>
        </div>
      </div>

      <div class="actions">
        <button class="btn primary" type="submit">Guardar</button>
        <a class="btn" href="<?= h(url('/members/index.php')) ?>">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php render_footer(); ?>
