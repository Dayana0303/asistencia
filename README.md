# Control de asistencia (PHP + MySQL + Bootstrap 5)

Sistema sencillo para administrar empleados y registrar entradas/salidas con cédula y código de acceso.

## Instalación (XAMPP / WAMP / Laragon)
1. Copie la carpeta del proyecto a `htdocs` (XAMPP) o `www`.
2. En phpMyAdmin, importe `sql/database.sql`.
3. Revise `config/db.php` (usuario y contraseña de MySQL).
4. Abra `http://localhost/asistencia/crear_admin.php` una sola vez y luego **elimine** ese archivo.
5. Ingrese a `http://localhost/asistencia/admin/login.php` con `admin` / `Admin123` (cambie la contraseña).

## Rutas
- `/index.php` – pantalla pública de marcación
- `/admin/login.php` – acceso administrativo
- `/admin/dashboard.php`, `/admin/empleados.php`, `/admin/asistencias.php`

## Seguridad
Contraseñas con `password_hash`, sesiones PHP, consultas preparadas (PDO), escape de salida, token CSRF y páginas admin protegidas.

## Extras
Exportar a Excel (CSV), empleados actualmente dentro, llegadas tardías.
