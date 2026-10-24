-- Base de datos del sistema de control de asistencia
CREATE DATABASE IF NOT EXISTS control_asistencia
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE control_asistencia;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,          -- hash con password_hash()
  estado TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS empleados (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cedula VARCHAR(20) NOT NULL UNIQUE,
  nombre VARCHAR(120) NOT NULL,
  correo VARCHAR(120) NOT NULL,
  telefono VARCHAR(20) NOT NULL,
  codigo_acceso VARCHAR(10) NOT NULL UNIQUE,
  estado TINYINT(1) NOT NULL DEFAULT 1,    -- 1 activo, 0 inactivo
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS asistencias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  empleado_id INT NOT NULL,
  fecha DATE NOT NULL,
  hora_entrada TIME NOT NULL,
  hora_salida TIME NULL,
  INDEX idx_fecha (fecha),
  INDEX idx_empleado_fecha (empleado_id, fecha),
  CONSTRAINT fk_asistencia_empleado FOREIGN KEY (empleado_id)
    REFERENCES empleados(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- El usuario administrador se crea abriendo en el navegador: /crear_admin.php
