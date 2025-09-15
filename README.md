-- Base de datos
--contraseña administrador: mi_contraseña_segura

CREATE DATABASE IF NOT EXISTS base_usuarios; 
USE base_usuarios; 

-- Tabla de administradores 
CREATE TABLE administradores ( 
    id INT AUTO_INCREMENT PRIMARY KEY, 
    adm_email VARCHAR(100) UNIQUE NOT NULL, 
    adm_name VARCHAR(100) NOT NULL, 
    adm_pass VARCHAR(255) NOT NULL
); 

-- Insertar admin por defecto
INSERT INTO administradores (adm_email, adm_name, adm_pass) VALUES 
('nuevo_admin@example.com', 'Administrador Principal', '$2y$10$dQjn.j1QNKpJn4n8QWLK0.Gx6u9kLR6eB14ql9vVUJYwBesjfx2lK');

-- Tabla de usuarios
CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    imagen VARCHAR(255) NULL,
    usr_email VARCHAR(255) UNIQUE NOT NULL, 
    usr_name VARCHAR(100) NOT NULL, 
    usr_pass VARCHAR(255) NULL,
    validado TINYINT NOT NULL DEFAULT 0
);

-- Tabla de pagos 
CREATE TABLE pagos ( 
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL, 
    fecha INT NOT NULL,  -- timestamp
    tipo ENUM('inicial','mensual') NOT NULL,
    imagen VARCHAR(255) NOT NULL, 
    validado TINYINT NOT NULL DEFAULT 0, -- 0=pte, 1=ok, 2=rej 
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) 
); 

-- Tabla de horas semanales 
CREATE TABLE horas_semanales ( 
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL, 
    fecha DATE NOT NULL,
    total_horas INT NOT NULL,
    justificacion TEXT NULL,
    tipo_solicitud ENUM('ninguna','exoneracion','pago_compensatorio') DEFAULT 'ninguna',
    comprobante VARCHAR(255) NULL,
    estado_validacion ENUM('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
    FOREIGN KEY (usuario_id) REFERENCES usuario(id)
);
