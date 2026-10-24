<?php
$titulo = 'Dashboard';
require_once __DIR__ . '/../includes/admin_header.php';

$pdo = db();
$hoy = date('Y-m-d');

$total   = (int)$pdo->query('SELECT COUNT(*) FROM empleados')->fetchColumn();
$activos = (int)$pdo->query('SELECT COUNT(*) FROM empleados WHERE estado = 1')->fetchColumn();

$q = $pdo->prepare('SELECT COUNT(DISTINCT empleado_id) FROM asistencias WHERE fecha = ?');
$q->execute([$hoy]);
$ingresaronHoy = (int)$q->fetchColumn();

$q = $pdo->prepare('SELECT COUNT(*) FROM asistencias WHERE fecha = ? AND hora_salida IS NULL');
$q->execute([$hoy]);
$dentro = (int)$q->fetchColumn();

$q = $pdo->prepare('SELECT COUNT(*) FROM asistencias WHERE fecha = ?');
$q->execute([$hoy]);
$registrosHoy = (int)$q->fetchColumn();

// Extra: empleados actualmente dentro
$q = $pdo->prepare(
    'SELECT e.cedula, e.nombre, a.hora_entrada
     FROM asistencias a JOIN empleados e ON e.id = a.empleado_id
     WHERE a.fecha = ? AND a.hora_salida IS NULL
     ORDER BY a.hora_entrada'
);
$q->execute([$hoy]);
$listaDentro = $q->fetchAll();

$tarjetas = [
    ['Total de empleados', $total, 'secondary'],
    ['Empleados activos', $activos, 'success'],
    ['Ingresaron hoy', $ingresaronHoy, 'primary'],
    ['Actualmente dentro', $dentro, 'warning'],
    ['Registros de hoy', $registrosHoy, 'info'],
];
?>
<h1 class="h3 mb-4">Dashboard</h1>
<div class="row g-3 mb-4">
  <?php foreach ($tarjetas as [$label, $valor, $color]): ?>
    <div class="col-6 col-md-4 col-lg">
      <div class="card border-<?= $color ?> h-100">
        <div class="card-body">
          <div class="text-muted small"><?= e($label) ?></div>
          <div class="display-6"><?= $valor ?></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="card-header">Empleados actualmente dentro</div>
  <div class="table-responsive">
    <table class="table table-sm mb-0">
      <thead><tr><th>Cédula</th><th>Empleado</th><th>Entrada</th></tr></thead>
      <tbody>
      <?php foreach ($listaDentro as $r): ?>
        <tr><td><?= e($r['cedula']) ?></td><td><?= e($r['nombre']) ?></td><td><?= fmt_hora($r['hora_entrada']) ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$listaDentro): ?>
        <tr><td colspan="3" class="text-muted text-center py-3">No hay empleados dentro en este momento.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
