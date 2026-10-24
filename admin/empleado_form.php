<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$pdo = db();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$editando = $id > 0;
$errores = [];
$datos = ['cedula' => '', 'nombre' => '', 'correo' => '', 'telefono' => '', 'codigo_acceso' => ''];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM empleados WHERE id = ?');
    $stmt->execute([$id]);
    $emp = $stmt->fetch();
    if (!$emp) {
        flash_set('danger', 'El empleado no existe.');
        header('Location: empleados.php');
        exit;
    }
    $datos = $emp;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['cedula']   = trim($_POST['cedula'] ?? '');
    $datos['nombre']   = trim($_POST['nombre'] ?? '');
    $datos['correo']   = trim($_POST['correo'] ?? '');
    $datos['telefono'] = trim($_POST['telefono'] ?? '');

    if (!csrf_valido()) {
        $errores[] = 'Sesión expirada. Recargue la página.';
    }
    if ($datos['cedula'] === '' || $datos['nombre'] === '' || $datos['correo'] === '' || $datos['telefono'] === '') {
        $errores[] = 'Todos los campos son obligatorios.';
    }
    if ($datos['cedula'] !== '' && !preg_match('/^[0-9\-]{5,20}$/', $datos['cedula'])) {
        $errores[] = 'La cédula solo puede contener números y guiones (5 a 20 caracteres).';
    }
    if ($datos['correo'] !== '' && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    }
    if ($datos['telefono'] !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $datos['telefono'])) {
        $errores[] = 'El teléfono no es válido.';
    }

    // Cédula única
    if (!$errores) {
        $stmt = $pdo->prepare('SELECT id FROM empleados WHERE cedula = ? AND id <> ?');
        $stmt->execute([$datos['cedula'], $id]);
        if ($stmt->fetch()) {
            $errores[] = 'Ya existe un empleado con esa cédula.';
        }
    }

    if (!$errores) {
        if ($editando) {
            $pdo->prepare('UPDATE empleados SET cedula=?, nombre=?, correo=?, telefono=? WHERE id=?')
                ->execute([$datos['cedula'], $datos['nombre'], $datos['correo'], $datos['telefono'], $id]);
            flash_set('success', 'Empleado actualizado correctamente.');
        } else {
            $codigo = generar_codigo($pdo);
            $pdo->prepare('INSERT INTO empleados (cedula, nombre, correo, telefono, codigo_acceso) VALUES (?,?,?,?,?)')
                ->execute([$datos['cedula'], $datos['nombre'], $datos['correo'], $datos['telefono'], $codigo]);
            flash_set('success', 'Empleado registrado. Código de acceso generado: <strong>' . e($codigo) . '</strong>');
        }
        header('Location: empleados.php');
        exit;
    }
}

$titulo = $editando ? 'Editar empleado' : 'Registrar empleado';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1 class="h3 mb-3"><?= e($titulo) ?></h1>

<?php if ($errores): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<div class="card" style="max-width: 640px;">
  <div class="card-body">
    <form method="post" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$id ?>">
      <div class="mb-3">
        <label for="cedula" class="form-label">Cédula</label>
        <input type="text" class="form-control" id="cedula" name="cedula" value="<?= e($datos['cedula']) ?>" required>
      </div>
      <div class="mb-3">
        <label for="nombre" class="form-label">Nombre completo</label>
        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>" required>
      </div>
      <div class="mb-3">
        <label for="correo" class="form-label">Correo</label>
        <input type="email" class="form-control" id="correo" name="correo" value="<?= e($datos['correo']) ?>" required>
      </div>
      <div class="mb-3">
        <label for="telefono" class="form-label">Teléfono</label>
        <input type="text" class="form-control" id="telefono" name="telefono" value="<?= e($datos['telefono']) ?>" required>
      </div>
      <?php if ($editando): ?>
        <div class="mb-3">
          <label class="form-label">Código de acceso</label>
          <input type="text" class="form-control" value="<?= e($datos['codigo_acceso']) ?>" disabled>
        </div>
      <?php else: ?>
        <p class="text-muted small">El código de acceso se genera automáticamente al guardar.</p>
      <?php endif; ?>
      <button class="btn btn-primary">Guardar</button>
      <a href="empleados.php" class="btn btn-link">Cancelar</a>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
