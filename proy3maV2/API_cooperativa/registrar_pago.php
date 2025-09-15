<?php
session_start(); // Inicia la sesión para poder usar variables de sesión

// Verificar sesión: si no existe usuario logueado, redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Front/login.html'); // Redirección al login si no hay sesión
    exit;
}

// Variables de sesión del usuario
$usuario_id = $_SESSION['usuario_id']; // ID del usuario logueado
$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : "Usuario"; // Nombre del usuario
$mensaje = "";       // Mensaje de éxito o error
$mensaje_tipo = "";  // Tipo de mensaje ('success' o 'error')

// Procesamiento del archivo cuando se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'config.php'; // Conexión a la base de datos

    // Verifica si hubo error al subir el archivo
    if ($_FILES["comprobante"]["error"] > 0) {
        $mensaje = "Error al subir el archivo"; // Mensaje de error
        $mensaje_tipo = "error";
    } else {
        // Carpeta donde se guardarán los archivos
        $directorio = "archivos/";
        if (!is_dir($directorio)) mkdir($directorio, 0777, true); // Si no existe, la crea

        // Nombre del archivo subido
        $nombre_archivo = $_FILES["comprobante"]["name"];
        $ruta_archivo = $directorio . $nombre_archivo; // Ruta completa del archivo
        move_uploaded_file($_FILES["comprobante"]["tmp_name"], $ruta_archivo); // Mueve el archivo a la carpeta

        // Datos recibidos del formulario
        $tipo_pago = $_POST["tipo_pago"]; // Tipo de pago seleccionado
        $fecha = $_POST["fecha"];         // Fecha del pago

        // Inserta el registro en la base de datos
        $query = "INSERT INTO pagos (usuario_id, fecha, tipo, imagen, validado) 
                  VALUES ('$usuario_id', '$fecha', '$tipo_pago', '$ruta_archivo', 0)";
        mysqli_query($conn, $query); // Ejecuta la consulta

        // Mensaje de éxito
        $mensaje = "Comprobante subido correctamente";
        $mensaje_tipo = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrar Pago</title>

<!-- Bootstrap y fuentes -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    body { font-family: 'Poppins', sans-serif; background: #f8f9fa; margin:0; }

    /* Sidebar lateral */
    .sidebar { position: fixed; top: 0; left: -280px; width: 280px; height: 100vh; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        transition: all 0.3s ease; z-index: 1000; overflow-y: auto; }
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
    .top-navbar { background: white; padding: 15px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; 
        border-radius: 10px; position: relative; }
    .menu-toggle { background: none; border: none; font-size: 1.2rem; color: #667eea; cursor: pointer; }
    .user-info { font-weight: 500; color: #333; }

    /* Mensajes flotantes */
    .alert-banner { display: flex; align-items: center; justify-content: center; gap: 10px; 
        padding: 10px 20px; border-radius: 10px; font-weight: 500; color: white; opacity: 0; 
        transform: translateY(-20px); transition: all 0.5s ease; position: absolute; 
        left: 50%; top: 50%; transform: translate(-50%, -50%) translateY(-20px); }
    .alert-success { background: linear-gradient(135deg,#28a745,#218838); }
    .alert-error { background: linear-gradient(135deg,#dc3545,#b02a37); }
    .alert-banner.show { opacity: 1; transform: translate(-50%, -50%) translateY(0); }

    /* Título */
    .page-title { font-size: 2rem; font-weight: 600; color: #333; margin-bottom: 20px; text-align:center; }

    /* Tarjeta del formulario */
    .card-form { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; 
        border-radius: 15px; padding: 30px; max-width: 500px; margin: auto; 
        transition: transform 0.3s, box-shadow 0.3s; }
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
        <div class="nav-item"><a href="panel_usuario.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-item"><a href="registrar_horas.php" class="nav-link"><i class="fas fa-clock"></i> Registrar Horas</a></div>
        <div class="nav-item"><a href="registrar_pago.php" class="nav-link active"><i class="fas fa-dollar-sign"></i> Registrar Pago</a></div>
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

        <!-- Banner de mensajes -->
        <?php if(!empty($mensaje)): ?>
        <div class="alert-banner <?= $mensaje_tipo==='success'?'alert-success':'alert-error' ?>" id="alertBanner">
            <i class="fas <?= $mensaje_tipo==='success'?'fa-check-circle':'fa-exclamation-circle' ?>"></i>
            <?= htmlspecialchars($mensaje) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Título -->
    <h1 class="page-title">Registrar Pago</h1>

    <!-- Formulario para subir comprobante -->
    <div class="card-form">
        <form action="registrar_pago.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Subir Comprobante</label>
                <input type="file" name="comprobante" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo de Pago</label>
                <select name="tipo_pago" class="form-select">
                    <option value="mensual">Mensual</option>
                    <option value="inicial">Inicial</option>
                </select>
            </div>
            <!-- Fecha se envía automáticamente como campo oculto -->
            <input type="hidden" name="fecha" value="<?= date("Y-m-d H:i:s") ?>">
            <button type="submit" class="btn-submit">Subir Comprobante</button>
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

    // Animación para mostrar mensajes flotantes
    window.addEventListener('DOMContentLoaded', () => {
        const banner = document.getElementById('alertBanner');
        if(banner){
            setTimeout(()=>banner.classList.add('show'), 100);   // Aparece
            setTimeout(()=>banner.classList.remove('show'), 5000); // Desaparece a los 5s
        }
    });
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
