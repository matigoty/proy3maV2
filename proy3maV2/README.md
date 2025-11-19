-- Base de datos
-- contraseña administrador: mi_contraseña_segura

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
    validado TINYINT NOT NULL DEFAULT 0,
    habilitado_unidad TINYINT(1) NOT NULL DEFAULT 0 
);

-- Tabla de pagos 
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    mes_pago VARCHAR(20) NOT NULL,             -- Mes al que corresponde el pago (ej: "Septiembre 2025")
    tipo ENUM('inicial','mensual') NOT NULL,   -- Tipo de pago
    imagen VARCHAR(255) NOT NULL,              -- Comprobante subido
    validado TINYINT NOT NULL DEFAULT 0,       -- 0=pte, 1=ok, 2=rej
    estado_cuenta ENUM('al_dia', 'atrasado') DEFAULT NULL, -- Estado general (sin estado por defecto)
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

-- Tabla de unidades_habitacionales
CREATE TABLE Unidad_habitacional (
    id_unidad INT PRIMARY KEY NOT NULL,
    numPuerta VARCHAR(255) NOT NULL,
    estado ENUM('ocupada','desocupada') DEFAULT 'desocupada',
    usuario_id INT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id)
);

INSERT INTO Unidad_habitacional (id_unidad, numPuerta, estado, usuario_id) VALUES
(1000, 'Puerta 1',  'desocupada', NULL ),
(1002, 'Puerta 2',  'desocupada', NULL),
(1003, 'Puerta 3',  'desocupada', NULL),
(1004, 'Puerta 4',  'desocupada', NULL),
(1005, 'Puerta 5',  'desocupada', NULL),
(1006, 'Puerta 6',  'desocupada', NULL),
(1007, 'Puerta 7',  'desocupada', NULL),
(1008, 'Puerta 8',  'desocupada', NULL),
(1009, 'Puerta 9',  'desocupada', NULL),
(2001, 'Puerta 10', 'desocupada', NULL),
(2002, 'Puerta 11', 'desocupada', NULL),
(2003, 'Puerta 12', 'desocupada', NULL),
(2004, 'Puerta 13', 'desocupada', NULL),
(2005, 'Puerta 14', 'desocupada', NULL),
(2006, 'Puerta 15', 'desocupada', NULL),
(2007, 'Puerta 16', 'desocupada', NULL),
(2008, 'Puerta 17', 'desocupada', NULL),
(2009, 'Puerta 18', 'desocupada', NULL),
(3001, 'Puerta 19', 'desocupada', NULL),
(3002, 'Puerta 20', 'desocupada', NULL),
(3003, 'Puerta 21', 'desocupada', NULL),
(3004, 'Puerta 22', 'desocupada', NULL),
(3005, 'Puerta 23', 'desocupada', NULL),
(3006, 'Puerta 24', 'desocupada', NULL),
(3007, 'Puerta 25', 'desocupada', NULL),
(3008, 'Puerta 26', 'desocupada', NULL),
(3009, 'Puerta 27', 'desocupada', NULL),
(4001, 'Puerta 28', 'desocupada', NULL),
(4002, 'Puerta 29', 'desocupada', NULL),
(4003, 'Puerta 30', 'desocupada', NULL);
