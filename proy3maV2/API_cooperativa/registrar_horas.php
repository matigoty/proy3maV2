<?php
// Inicia la sesión
session_start();

// Si no hay usuario logueado, redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Front/login.html');
    exit;
}

// Conexión a la base de datos
require 'config.php'; 

// Variables para mensajes de éxito o error
$mensaje = "";
$mensaje_tipo = ""; // 'success' o 'error'

// Recupera el nombre del usuario desde la sesión
$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : "Usuario";

// Si se envió el formulario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];   // ID del usuario logueado
    $fecha = $_POST['fecha'];                // Fecha ingresada
    $total_horas = intval($_POST['total_horas']); // Total de horas trabajadas
    $justificacion = !empty($_POST['justificacion']) ? $_POST['justificacion'] : null; // Texto opcional
    $tipo_solicitud = $_POST['tipo_solicitud']; // Tipo de solicitud (ninguna, exoneración, compensatorio)

    // Manejo del comprobante (archivo) solo si es "compensatorio"
    $comprobante = null;
    $directorio = "uploads/";
    if (!is_dir($directorio)) mkdir($directorio, 0777, true);

    if ($tipo_solicitud === 'compensatorio' && isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === 0) {
        $nombre_archivo = time() . "_" . basename($_FILES['comprobante']['name']);
        $ruta = $directorio . $nombre_archivo;
        if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $ruta)) {
            $comprobante = $ruta;
        } else {
            $mensaje = "Error al subir el archivo.";
            $mensaje_tipo = "error";
        }
    }

    // Inserta los datos en la tabla horas_semanales
    $sql = "INSERT INTO horas_semanales (usuario_id, fecha, total_horas, justificacion, tipo_solicitud, comprobante) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isisss", $usuario_id, $fecha, $total_horas, $justificacion, $tipo_solicitud, $comprobante);

    // Verifica si se insertó correctamente
    if ($stmt->execute()) {
        $mensaje = "¡Tus horas se registraron correctamente!";
        $mensaje_tipo = "success";
    } else {
        $mensaje = "Error al registrar horas: " . $stmt->error;
        $mensaje_tipo = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrar Horas</title>

<!-- Bootstrap y fuentes -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    /* Estilos generales */
    body { font-family: 'Poppins', sans-serif; background: #f8f9fa; margin:0; }

    /* Sidebar lateral */
    .sidebar { position: fixed; top: 0; left: -280px; width: 280px; height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); transition: all 0.3s ease; z-index: 1000; overflow-y: auto; }
    .sidebar.active { left: 0; }
    .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .sidebar-brand { color: white; font-size: 1.5rem; font-weight: 700; text-decoration: none; }
    .nav-link { display: flex; align-items: center; padding: 12px 20px; color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s ease; border-left: 3px solid transparent; }
    .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; border-left-color: white; }
    .nav-link i { width: 20px; margin-right: 10px; }

    /* Contenido principal */
    .main-content { margin-left: 0; transition: all 0.3s ease; min-height: 100vh; padding: 20px; width: 100%; }
    .main-content.sidebar-open { margin-left: 280px; } 

    /* Barra superior */
    .top-navbar { background: white; padding: 15px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-radius: 10px; position: relative; }
    .menu-toggle { background: none; border: none; font-size: 1.2rem; color: #667eea; cursor: pointer; }
    .user-info { font-weight: 500; color: #333; }

    /* Mensajes flotantes */
    .alert-banner { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 10px 20px; border-radius: 10px; font-weight: 500; color: white; opacity: 0; transform: translateY(-20px); transition: all 0.5s ease; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%) translateY(-20px); }
    .alert-success { background: linear-gradient(135deg,#28a745,#218838); }
    .alert-error { background: linear-gradient(135deg,#dc3545,#b02a37); }
    .alert-banner.show { opacity: 1; transform: translate(-50%, -50%) translateY(0); }

    /* Título */
    .page-title { font-size: 2rem; font-weight: 600; color: #333; margin-bottom: 20px; text-align:center; }

    /* Tarjeta formulario */
    .card-form { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px; padding: 30px; max-width: 600px; margin: auto; transition: transform 0.3s, box-shadow 0.3s; }
    .card-form:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }

    .form-label { font-weight: 500; color: white; }
    .form-control, .form-select { border-radius: 8px; }

    /* Botón */
    .btn-submit { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 8px; width: 100%; margin-top: 15px; transition: background 0.3s; }
    .btn-submit:hover { background: #218838; }

    /* Responsivo */
    @media (max-width: 768px) { 
        .main-content.sidebar-open { margin-left: 0; width: 100%; } 
        .sidebar { left: -100%; } 
        .sidebar.active { left: 0; width: 100%; } 
    }
</style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-brand"><i class="fas fa-user-clock"></i> Usuario</a>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-item"><a href="panel_usuario.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-item"><a href="registrar_horas.php" class="nav-link"><i class="fas fa-clock"></i> Registrar Horas</a></div>
        <div class="nav-item"><a href="registrar_pago.php" class="nav-link"><i class="fas fa-dollar-sign"></i> Registrar Pago</a></div>
        <div class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></div>
    </nav>
</div>

<!-- Contenido principal -->
<div class="main-content" id="mainContent">
    <div class="top-navbar">
        <!-- Botón menú -->
        <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
        <!-- Nombre usuario -->
        <div class="user-info"><?= htmlspecialchars($usuario_nombre) ?></div>

        <!-- Mensaje si existe -->
        <?php if(!empty($mensaje)): ?>
        <div class="alert-banner <?= $mensaje_tipo==='success'?'alert-success':'alert-error' ?>" id="alertBanner">
            <i class="fas <?= $mensaje_tipo==='success'?'fa-check-circle':'fa-exclamation-circle' ?>"></i>
            <?= htmlspecialchars($mensaje) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Título -->
    <h1 class="page-title">Registrar Horas</h1>

    <!-- Formulario -->
    <div class="card-form">
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Semana (ej: 2025-09-13)</label>
                <input type="date" name="fecha" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Total de horas trabajadas</label>
                <input type="number" name="total_horas" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">¿Necesitás justificar horas no trabajadas?</label>
                <textarea name="justificacion" class="form-control" rows="2"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Tipo de solicitud</label>
                <select name="tipo_solicitud" class="form-select">
                    <option value="ninguna">Ninguna</option>
                    <option value="exoneracion">Exoneración</option>
                    <option value="compensatorio">Pago compensatorio</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Comprobante (si es pago compensatorio)</label>
                <input type="file" name="comprobante" class="form-control">
            </div>

            <button type="submit" class="btn-submit">Guardar</button>
        </form>
    </div>
</div>

<!-- Scripts -->
<script>
    // Abrir/cerrar sidebar
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        mainContent.classList.toggle('sidebar-open');
    });

    // Animación de mensajes
    window.addEventListener('DOMContentLoaded', () => {
        const banner = document.getElementById('alertBanner');
        if(banner){
            setTimeout(()=>banner.classList.add('show'), 100);
            setTimeout(()=>banner.classList.remove('show'), 5000);
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
