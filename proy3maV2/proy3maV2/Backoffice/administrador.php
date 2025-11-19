<?php
session_start();

// Validar que el admin esté logueado
if (!isset($_SESSION['adm_id'])) {
    header("Location: index.php");
    exit;
}

// Manejar logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice - Cooperativa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }

        /* Sidebar */
        .sidebar { position: fixed; top: 0; left: -280px; width: 280px; height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); transition: all 0.3s ease; z-index: 1000; overflow-y: auto; }
        .sidebar.active { left: 0; }
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand { color: white; font-size: 1.5rem; font-weight: 700; text-decoration: none; }

        .sidebar-nav .nav-item { margin-top: 10px; }
        .nav-link { display: flex; align-items: center; padding: 12px 20px; color: rgba(255,255,255,0.85); text-decoration: none; transition: all 0.3s ease; border-left: 3px solid transparent; border-radius: 0 10px 10px 0; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; border-left-color: white; }
        .nav-link i { width: 20px; margin-right: 10px; }

        /* Main content */
        .main-content { margin-left: 0; transition: all 0.3s ease; min-height: 100vh; padding: 20px; }
        .main-content.sidebar-open { margin-left: 280px; }
        .top-navbar { background: white; padding: 15px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between; border-radius: 10px; margin-bottom: 20px; }
        .menu-toggle { background: none; border: none; font-size: 1.2rem; color: #667eea; cursor: pointer; }
        .user-info { font-weight: 500; color: #333; }

        /* Cards for links */
        .link-card { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); padding: 20px; text-align: center; transition: all 0.3s ease; text-decoration: none; color: #333; display: block; }
        .link-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .link-card i { font-size: 2rem; margin-bottom: 10px; display: block; }
        .link-card span { font-weight: 600; font-size: 1.1rem; }

        .grid-links { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 20px; }

        /* Overlay */
        .overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:999; display:none; }
        .overlay.active { display:block; }

        @media (max-width:768px){
            .main-content.sidebar-open { margin-left:0; }
            .sidebar { left:-100%; }
            .sidebar.active { left:0; width:100%; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand"><i class="fas fa-shield-alt"></i> Admin Cooperativa</a>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-item"><a href="usuarios.php" class="nav-link"><i class="fas fa-users"></i> Validar Usuarios</a></div>
            <div class="nav-item"><a href="pagos.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Validar Pagos</a></div>
            <div class="nav-item"><a href="horas.php" class="nav-link"><i class="fas fa-clock"></i> Validar Horas</a></div>
            <div class="nav-item"><a href="unidades_habitacionales.php" class="nav-link"><i class="fas fa-home"></i> Unidades Habitacionales</a></div>
            <div class="nav-item"><a href="?logout=true" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></div>
        </nav>
    </div>

    <div class="overlay" id="overlay"></div>

    <!-- Main content -->
    <div class="main-content" id="mainContent">
        <div class="top-navbar">
            <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            <div class="user-info">Administrador: <?php echo $_SESSION['adm_name'] ?? 'Admin'; ?></div>
        </div>

        <h1 class="mb-4">Panel de Administración</h1>
        <div class="grid-links">
            <a href="usuarios.php" class="link-card">
                <i class="fas fa-users"></i>
                <span>Validar Usuarios</span>
            </a>
            <a href="pagos.php" class="link-card">
                <i class="fas fa-money-bill-wave"></i>
                <span>Validar Pagos</span>
            </a>
            <a href="horas.php" class="link-card">
                <i class="fas fa-clock"></i>
                <span>Validar Horas</span>
            </a>
            <a href="unidades_habitacionales.php" class="link-card">
                <i class="fas fa-home"></i>
                <span>Unidades Habitacionales</span>
            </a>
            <a href="?logout=true" class="link-card text-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const overlay = document.getElementById('overlay');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('sidebar-open');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            mainContent.classList.remove('sidebar-open');
            overlay.classList.remove('active');
        });
    </script>
</body>
</html>
