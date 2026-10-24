<?php
require_once __DIR__ . '/includes/functions.php';

$resultado = null; // ['tipo' => 'success|danger|warning', 'lineas' => [...]]
$cedula = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $codigo = strtoupper(trim($_POST['codigo'] ?? ''));

    if (!csrf_valido()) {
        $resultado = ['tipo' => 'danger', 'lineas' => ['Sesión expirada. Recargue la página e intente de nuevo.']];
    } elseif ($cedula === '' || $codigo === '') {
        $resultado = ['tipo' => 'warning', 'lineas' => ['La cédula y el código de acceso son obligatorios.']];
    } else {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM empleados WHERE cedula = ? AND codigo_acceso = ?');
        $stmt->execute([$cedula, $codigo]);
        $emp = $stmt->fetch();

        if (!$emp) {
            $resultado = ['tipo' => 'danger', 'lineas' => ['Cédula o código de acceso incorrecto.']];
        } elseif ((int)$emp['estado'] !== 1) {
            $resultado = ['tipo' => 'danger', 'lineas' => ['El empleado se encuentra inactivo. Contacte al administrador.']];
        } else {
            $hoy = date('Y-m-d');
            $ahora = date('H:i:s');

            // ¿Tiene una entrada abierta hoy (sin salida)?
            $abierta = $pdo->prepare(
                'SELECT id FROM asistencias
                 WHERE empleado_id = ? AND fecha = ? AND hora_salida IS NULL
                 ORDER BY id DESC LIMIT 1'
            );
            $abierta->execute([$emp['id'], $hoy]);
            $registro = $abierta->fetch();

            if ($registro) {
                // Corresponde SALIDA
                $pdo->prepare('UPDATE asistencias SET hora_salida = ? WHERE id = ?')
                    ->execute([$ahora, $registro['id']]);
                $resultado = ['tipo' => 'success', 'lineas' => [
                    'Empleado: ' . $emp['nombre'],
                    'Fecha: ' . fmt_fecha($hoy),
                    'Hora de salida: ' . fmt_hora12($ahora),
                    'Salida registrada correctamente.',
                ]];
            } else {
                // Corresponde ENTRADA
                $pdo->prepare('INSERT INTO asistencias (empleado_id, fecha, hora_entrada) VALUES (?, ?, ?)')
                    ->execute([$emp['id'], $hoy, $ahora]);
                $resultado = ['tipo' => 'success', 'lineas' => [
                    'Empleado: ' . $emp['nombre'],
                    'Fecha: ' . fmt_fecha($hoy),
                    'Hora de entrada: ' . fmt_hora12($ahora),
                    'Entrada registrada correctamente.',
                ]];
            }
            $cedula = ''; // limpiar el formulario tras un registro exitoso
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrar asistencia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 460px;">
  <div class="text-center mb-4">
    <h1 class="display-4 fw-semibold mb-0" id="reloj">--:--:--</h1>
    <div class="text-muted" id="fecha"></div>
  </div>

  <?php if ($resultado): ?>
    <div class="alert alert-<?= e($resultado['tipo']) ?>">
      <?php foreach ($resultado['lineas'] as $l): ?>
        <div><?= e($l) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h2 class="h5 mb-3">Registrar entrada o salida</h2>
      <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label for="cedula" class="form-label">Cédula</label>
          <input type="text" class="form-control form-control-lg" id="cedula" name="cedula"
                 value="<?= e($cedula) ?>" required autofocus>
        </div>
        <div class="mb-3">
          <label for="codigo" class="form-label">Código de acceso</label>
          <input type="password" class="form-control form-control-lg text-uppercase" id="codigo"
                 name="codigo" maxlength="10" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100">Registrar</button>
      </form>
    </div>
  </div>
  <p class="text-center mt-4"><a href="admin/login.php" class="text-muted small">Acceso administrativo</a></p>
</div>

<script>
function actualizarReloj() {
  const d = new Date();
  document.getElementById('reloj').textContent = d.toLocaleTimeString('es-CR');
  document.getElementById('fecha').textContent =
    d.toLocaleDateString('es-CR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}
actualizarReloj();
setInterval(actualizarReloj, 1000);
</script>
</body>
</html>
