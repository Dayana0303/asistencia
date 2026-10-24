<?php
// EJECUTAR UNA SOLA VEZ y luego ELIMINAR este archivo.
require_once __DIR__ . '/includes/functions.php';

$pdo = db();
$existe = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();

if ($existe > 0) {
    exit('Ya existe un usuario administrador. Elimine este archivo (crear_admin.php).');
}

$password = 'Admin123';
$pdo->prepare('INSERT INTO usuarios (nombre, usuario, password, estado) VALUES (?, ?, ?, 1)')
    ->execute(['Administrador', 'admin', password_hash($password, PASSWORD_DEFAULT)]);

echo 'Administrador creado. Usuario: admin | Contraseña: Admin123<br>';
echo '<strong>Elimine ahora este archivo (crear_admin.php).</strong> ';
echo '<a href="admin/login.php">Ir al login</a>';
