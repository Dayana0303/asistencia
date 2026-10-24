<?php
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_valido()) {
        $error = 'Sesión expirada. Recargue la página.';
    } elseif ($usuario === '' || $password === '') {
        $error = 'Usuario y contraseña son obligatorios.';
    } else {
        $stmt = db()->prepare('SELECT * FROM usuarios WHERE usuario = ? AND estado = 1');
        $stmt->execute([$usuario]);
        $u = $stmt->fetch();

        if ($u && password_verify($password, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $u['id'];
            $_SESSION['admin_nombre'] = $u['nombre'];
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Acceso administrativo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 420px;">
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h1 class="h4 mb-3">Acceso administrativo</h1>
      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label for="usuario" class="form-label">Usuario</label>
          <input type="text" class="form-control" id="usuario" name="usuario" value="<?= e($usuario) ?>" required autofocus>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button class="btn btn-dark w-100">Iniciar sesión</button>
      </form>
    </div>
  </div>
  <p class="text-center mt-3"><a href="../index.php" class="text-muted small">Ir a la pantalla de marcación</a></p>
</div>
</body>
</html>
