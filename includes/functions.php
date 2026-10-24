<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Escapa texto para mostrarlo en HTML (previene XSS)
function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Protección CSRF para formularios
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function csrf_valido(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

// Protege páginas administrativas
function require_login(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Genera un código único de 6 caracteres, ej: A7F294
function generar_codigo(PDO $pdo): string {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM empleados WHERE codigo_acceso = ?');
    do {
        $codigo = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $stmt->execute([$codigo]);
    } while ($stmt->fetchColumn() > 0);
    return $codigo;
}

// Formatos
function fmt_fecha(string $f): string  { return date('d/m/Y', strtotime($f)); }
function fmt_hora(?string $h): string  { return $h ? date('H:i', strtotime($h)) : '—'; }
function fmt_hora12(string $h): string { return date('h:i A', strtotime($h)); }

// Horas trabajadas en formato H:MM
function horas_trabajadas(string $entrada, ?string $salida): string {
    if (!$salida) return '—';
    $seg = max(0, strtotime($salida) - strtotime($entrada));
    return floor($seg / 3600) . ':' . str_pad((string)floor(($seg % 3600) / 60), 2, '0', STR_PAD_LEFT);
}

// Mensajes flash (se muestran una sola vez)
function flash_set(string $tipo, string $msg): void {
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg];
}
function flash_get(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
