<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

// Mismo patrón de validación de fechas que asistencias.php
$esFecha = fn($v) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) === 1;

// Por defecto: del primer día del mes hasta hoy
$desde = trim($_GET['desde'] ?? date('Y-m-01'));
$hasta = trim($_GET['hasta'] ?? date('Y-m-d'));
if (!$esFecha($desde)) { $desde = date('Y-m-01'); }
if (!$esFecha($hasta)) { $hasta = date('Y-m-d'); }

// Suma las horas de cada empleado (solo registros con salida)
$stmt = db()->prepare(
    'SELECT e.cedula, e.nombre,
            COUNT(DISTINCT a.fecha) AS dias,
            SUM(TIME_TO_SEC(a.hora_salida) - TIME_TO_SEC(a.hora_entrada)) AS segundos
     FROM asistencias a
     JOIN empleados e ON e.id = a.empleado_id
     WHERE a.hora_salida IS NOT NULL AND a.fecha >= ? AND a.fecha <= ?
     GROUP BY e.id, e.cedula, e.nombre
     ORDER BY segundos DESC'
);
$stmt->execute([$desde, $hasta]);
$filas = $stmt->fetchAll();

// Convierte segundos a formato H:MM (ej. 9:06)
function segundos_a_horas(int $seg): string {
    return floor($seg / 3600) . ':' . str_pad((string)floor(($seg % 3600) / 60), 2, '0', STR_PAD_LEFT);
}

$titulo = 'Reporte de horas';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1 class="h3 mb-3">Reporte de horas por empleado</h1>

<form class="card card-body mb-3" method="get">
  <div class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="form-label small">Desde</label>
      <input type="date" name="desde" class="form-control" value="<?= e($desde) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label small">Hasta</label>
      <input type="date" name="hasta" class="form-control" value="<?= e($hasta) ?>">
    </div>
    <div class="col-md-3">
      <button class="btn btn-primary">Filtrar</button>
    </div>
  </div>
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr><th>Cédula</th><th>Empleado</th><th>Días trabajados</th><th>Total de horas</th><th>Promedio por día</th></tr>
      </thead>
      <tbody>
      <?php foreach ($filas as $f): ?>
        <tr>
          <td><?= e($f['cedula']) ?></td>
          <td><?= e($f['nombre']) ?></td>
          <td><?= (int)$f['dias'] ?></td>
          <td><?= segundos_a_horas((int)$f['segundos']) ?></td>
          <td><?= segundos_a_horas((int)round($f['segundos'] / $f['dias'])) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$filas): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No hay registros completos en ese rango.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>