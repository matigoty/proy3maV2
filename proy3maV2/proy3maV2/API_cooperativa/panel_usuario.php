<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html"); // redirige al login si no hay sesión
    exit;
}

require_once __DIR__ . "/config.php";

// Obtener nombre del usuario logueado
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT usr_name FROM usuario WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($usuario_nombre);
$stmt->fetch(); 
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel de Usuario</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    body { 
        font-family: 'Poppins', sans-serif; 
        background: #ffffff; 
        margin: 0; 
        min-height: 100vh; 
    }
    .sidebar { position: fixed; top: 0; left: -280px; width: 280px; height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); transition: all 0.3s ease; z-index: 1000; overflow-y: auto; }
    .sidebar.active { left: 0; }
    .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .sidebar-brand { color: white; font-size: 1.5rem; font-weight: 700; text-decoration: none; }
    .nav-link { display: flex; align-items: center; padding: 12px 20px; color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s ease; border-left: 3px solid transparent; }
    .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; border-left-color: white; }
    .nav-link i { width: 20px; margin-right: 10px; }
    .main-content { margin-left: 0; transition: all 0.3s ease; min-height: 100vh; padding: 20px; width: 100%; }
    .main-content.sidebar-open { margin-left: 280px; } 
    .top-navbar { background: #ffffff; padding: 15px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-radius: 10px; }
    .menu-toggle { background: none; border: none; font-size: 1.2rem; color: #667eea; cursor: pointer; }
    .page-title { font-size: 2rem; font-weight: 600; color: #333; margin-bottom: 20px; }

    /* Tarjetas */
    .card { border-radius: 15px; color: white; text-align: center; transition: transform 0.3s, box-shadow 0.3s; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .card:hover { transform: translateY(-8px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
    .card-body i { font-size: 3rem; margin-bottom: 15px; }
    .card-body h5 { font-weight: 600; margin-bottom: 10px; }
    .card-body p { color: #e0e0e0; }
    .card-body a { text-decoration: none; color: white; background: rgba(0,0,0,0.2); padding: 8px 15px; border-radius: 8px; display: inline-block; margin-top: 10px; transition: background 0.3s; }
    .card-body a:hover { background: rgba(0,0,0,0.35); }

    @media (max-width: 768px) { 
        .main-content.sidebar-open { margin-left: 0; width: 100%; } 
        .sidebar { left: -100%; } 
        .sidebar.active { left: 0; width: 100%; } 
    }
</style>
</head>
<body>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-brand"><i class="fas fa-user-circle"></i> Usuario</a>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-item"><a href="panel_usuario.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-item"><a href="registrar_horas.php" class="nav-link"><i class="fas fa-clock"></i> Registrar Horas</a></div>
        <div class="nav-item"><a href="registrar_pago.php" class="nav-link"><i class="fas fa-dollar-sign"></i> Registrar Pago</a></div>
        <div class="nav-item"><a href="perfil.php" class="nav-link"><i class="fas fa-user-cog"></i> Gestionar Cuenta</a></div>
        <div class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></div>
    </nav>
</div>

<div class="main-content" id="mainContent">
    <div class="top-navbar">
        <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
        <div class="user-info"><span style="color:#333;">Bienvenido, <?= htmlspecialchars($usuario_nombre) ?></span></div>
    </div>

    <h1 class="page-title">Panel de Usuario</h1>

    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <div class="card p-4">
                <div class="card-body">
                    <i class="fas fa-clock"></i>
                    <h5>Registrar Horas</h5>
                    <p>Sube tus horas trabajadas semanalmente y justificaciones.</p>
                    <a href="registrar_horas.php">Ir</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card p-4">
                <div class="card-body">
                    <i class="fas fa-dollar-sign"></i>
                    <h5>Registrar Pago</h5>
                    <p>Sube tus comprobantes de pago y solicita validación.</p>
                    <a href="registrar_pago.php">Ir</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card p-4">
                <div class="card-body">
                    <i class="fas fa-user-cog"></i>
                    <h5>Gestionar Cuenta</h5>
                    <p>Consulta tus datos personales y estado de cuenta.</p>
                    <a href="perfil.php">Ir</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');

    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        mainContent.classList.toggle('sidebar-open');
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
