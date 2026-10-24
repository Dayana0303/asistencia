<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$esFecha = fn($v) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) === 1;

$empleado = trim($_GET['empleado'] ?? '');
$cedula   = trim($_GET['cedula'] ?? '');
$fecha    = trim($_GET['fecha'] ?? '');
$desde    = trim($_GET['desde'] ?? '');
$hasta    = trim($_GET['hasta'] ?? '');

$where = [];
$params = [];
if ($empleado !== '') { $where[] = 'e.nombre LIKE ?';  $params[] = "%$empleado%"; }
if ($cedula !== '')   { $where[] = 'e.cedula LIKE ?';  $params[] = "%$cedula%"; }
if ($esFecha($fecha)) { $where[] = 'a.fecha = ?';      $params[] = $fecha; }
if ($esFecha($desde)) { $where[] = 'a.fecha >= ?';     $params[] = $desde; }
if ($esFecha($hasta)) { $where[] = 'a.fecha <= ?';     $params[] = $hasta; }

$sql = 'SELECT a.*, e.cedula, e.nombre
        FROM asistencias a JOIN empleados e ON e.id = a.empleado_id'
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . ' ORDER BY a.fecha DESC, a.hora_entrada DESC LIMIT 500';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$registros = $stmt->fetchAll();

// Extra: exportar a CSV (se abre directamente en Excel)
if (isset($_GET['exportar'])) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="asistencias_' . date('Ymd_His') . '.csv"');
    echo "\xEF\xBB\xBF"; // BOM para acentos en Excel
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Cédula', 'Empleado', 'Fecha', 'Entrada', 'Salida', 'Horas trabajadas'], ';');
    foreach ($registros as $r) {
        fputcsv($out, [
            $r['cedula'], $r['nombre'], fmt_fecha($r['fecha']), fmt_hora($r['hora_entrada']),
            fmt_hora($r['hora_salida']), horas_trabajadas($r['hora_entrada'], $r['hora_salida']),
        ], ';');
    }
    fclose($out);
    exit;
}

$titulo = 'Historial de asistencia';
require __DIR__ . '/../includes/admin_header.php';
$qs = http_build_query(array_filter($_GET, fn($k) => $k !== 'exportar', ARRAY_FILTER_USE_KEY));
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h3 mb-0">Historial de asistencia</h1>
  <a href="?<?= e($qs) ?>&exportar=1" class="btn btn-success">Exportar a Excel (CSV)</a>
</div>

<form class="card card-body mb-3" method="get">
  <div class="row g-2">
    <div class="col-md-3"><label class="form-label small">Empleado</label>
      <input type="text" name="empleado" class="form-control" value="<?= e($empleado) ?>"></div>
    <div class="col-md-3"><label class="form-label small">Cédula</label>
      <input type="text" name="cedula" class="form-control" value="<?= e($cedula) ?>"></div>
    <div class="col-md-2"><label class="form-label small">Fecha</label>
      <input type="date" name="fecha" class="form-control" value="<?= e($fecha) ?>"></div>
    <div class="col-md-2"><label class="form-label small">Desde</label>
      <input type="date" name="desde" class="form-control" value="<?= e($desde) ?>"></div>
    <div class="col-md-2"><label class="form-label small">Hasta</label>
      <input type="date" name="hasta" class="form-control" value="<?= e($hasta) ?>"></div>
  </div>
  <div class="mt-3">
    <button class="btn btn-primary">Filtrar</button>
    <a href="asistencias.php" class="btn btn-link">Limpiar</a>
  </div>
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr><th>Cédula</th><th>Empleado</th><th>Fecha</th><th>Entrada</th><th>Salida</th><th>Horas trabajadas</th></tr>
      </thead>
      <tbody>
      <?php foreach ($registros as $r): ?>
        <tr>
          <td><?= e($r['cedula']) ?></td>
          <td><?= e($r['nombre']) ?></td>
          <td><?= fmt_fecha($r['fecha']) ?></td>
          <td>
            <?= fmt_hora($r['hora_entrada']) ?>
            <?php if ($r['hora_entrada'] > HORA_LIMITE_ENTRADA): ?>
              <span class="badge text-bg-warning">Tarde</span>
            <?php endif; ?>
          </td>
          <td><?= $r['hora_salida'] ? fmt_hora($r['hora_salida']) : '<span class="badge text-bg-info">Dentro</span>' ?></td>
          <td><?= horas_trabajadas($r['hora_entrada'], $r['hora_salida']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$registros): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No hay registros con esos filtros.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<p class="text-muted small mt-2">Se muestran hasta 500 registros. Llegada tardía: después de las <?= fmt_hora(HORA_LIMITE_ENTRADA) ?>.</p>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
