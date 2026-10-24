<?php
$titulo = 'Empleados';
require_once __DIR__ . '/../includes/admin_header.php';

$buscar = trim($_GET['q'] ?? '');
$sql = 'SELECT * FROM empleados';
$params = [];
if ($buscar !== '') {
    $sql .= ' WHERE nombre LIKE ? OR cedula LIKE ?';
    $params = ["%$buscar%", "%$buscar%"];
}
$sql .= ' ORDER BY nombre';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$empleados = $stmt->fetchAll();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h3 mb-0">Empleados</h1>
  <a href="empleado_form.php" class="btn btn-primary">Registrar empleado</a>
</div>

<form class="row g-2 mb-3" method="get">
  <div class="col-sm-8 col-md-5">
    <input type="text" name="q" class="form-control" placeholder="Buscar por nombre o cédula" value="<?= e($buscar) ?>">
  </div>
  <div class="col-auto"><button class="btn btn-outline-secondary">Buscar</button></div>
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Cédula</th><th>Nombre</th><th>Correo</th><th>Teléfono</th>
          <th>Código</th><th>Estado</th><th>Registro</th><th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($empleados as $emp): ?>
        <tr>
          <td><?= e($emp['cedula']) ?></td>
          <td><?= e($emp['nombre']) ?></td>
          <td><?= e($emp['correo']) ?></td>
          <td><?= e($emp['telefono']) ?></td>
          <td><code><?= e($emp['codigo_acceso']) ?></code></td>
          <td>
            <span class="badge text-bg-<?= $emp['estado'] ? 'success' : 'secondary' ?>">
              <?= $emp['estado'] ? 'Activo' : 'Inactivo' ?>
            </span>
          </td>
          <td><?= fmt_fecha($emp['fecha_registro']) ?></td>
          <td class="text-end text-nowrap">
            <a href="empleado_form.php?id=<?= (int)$emp['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
            <form method="post" action="empleado_toggle.php" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$emp['id'] ?>">
              <button class="btn btn-sm btn-outline-<?= $emp['estado'] ? 'danger' : 'success' ?>">
                <?= $emp['estado'] ? 'Desactivar' : 'Activar' ?>
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$empleados): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No hay empleados para mostrar.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
