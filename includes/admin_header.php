<?php
require_once __DIR__ . '/functions.php';
require_login();
$pagina = basename($_SERVER['PHP_SELF']);
$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo ?? 'Administración') ?> | Control de asistencia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Control de asistencia</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= $pagina==='dashboard.php'?'active':'' ?>" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= in_array($pagina,['empleados.php','empleado_form.php'])?'active':'' ?>" href="empleados.php">Empleados</a></li>
        <li class="nav-item"><a class="nav-link <?= $pagina==='asistencias.php'?'active':'' ?>" href="asistencias.php">Asistencias</a></li>
        <li class="nav-item"><a class="nav-link" href="../index.php" target="_blank">Pantalla de marcación</a></li>
      </ul>
      <span class="navbar-text me-3">Hola, <?= e($_SESSION['admin_nombre']) ?></span>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Cerrar sesión</a>
    </div>
  </div>
</nav>
<main class="container py-4">
<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['tipo']) ?> alert-dismissible fade show">
    <?= $flash['msg'] /* ya viene escapado desde quien lo crea */ ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
