<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido()) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        db()->prepare('UPDATE empleados SET estado = 1 - estado WHERE id = ?')->execute([$id]);
        flash_set('success', 'Estado del empleado actualizado.');
    }
} else {
    flash_set('danger', 'Solicitud no válida.');
}
header('Location: empleados.php');
exit;
