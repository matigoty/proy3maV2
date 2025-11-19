<?php
session_start();

// Validar que el admin esté logueado
if (!isset($_SESSION['adm_id'])) {
    header("Location: index.php");
    exit;
}
require_once("../API/config.php");

$conn = $conn; // conexión ya creada desde config.php

// Aprobar usuario
if (isset($_GET['aprobar'])) {
    $id = intval($_GET['aprobar']);
    mysqli_query($conn, "UPDATE usuario SET validado = 1 WHERE id = $id");
}

// Rechazar usuario
if (isset($_GET['rechazar'])) {
    $id = intval($_GET['rechazar']);
    mysqli_query($conn, "UPDATE usuario SET validado = 2 WHERE id = $id");
}

// Obtener usuarios
$result = mysqli_query($conn, "SELECT * FROM usuario");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Usuarios</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    body { 
        font-family: 'Poppins', sans-serif; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        margin:0; min-height: 100vh;
    }
    .sidebar {
        width: 280px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        position: fixed;
        top: 0; left: -280px;
        height: 100%;
        padding-top: 20px;
        transition: all 0.3s ease;
        z-index: 1000;
        overflow-y: auto;
    }
    .sidebar.active { left: 0; }
    .sidebar a {
        color: white; display: flex; align-items: center;
        padding: 12px 20px; text-decoration: none;
        transition: background 0.3s; border-left: 3px solid transparent;
    }
    .sidebar a i { width: 20px; margin-right: 10px; }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); border-left-color: #fff; }
    .main-content { margin-left: 0; transition: all 0.3s ease; min-height: 100vh; padding: 20px; }
    .main-content.sidebar-open { margin-left: 280px; }
    .top-navbar { background: white; padding: 15px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between; border-radius: 10px; margin-bottom: 20px; }
    .menu-toggle { font-size: 22px; cursor: pointer; color: #667eea; border: none; background: none; }
    .user-info { color:#764ba2; font-weight:500; }
    .page-title { font-size: 2rem; font-weight: 600; color: white; margin-bottom: 20px; }
    table th { background: #667eea; color: white; border: none; text-align: center; }
    table td, table th { vertical-align: middle; text-align: center; }
    .btn-approve { background: #28a745; color: white; border-radius: 5px; padding: 5px 10px; margin-right: 5px; text-decoration: none; display:inline-block; }
    .btn-reject { background: #dc3545; color: white; border-radius: 5px; padding: 5px 10px; text-decoration: none; display:inline-block; }
    .card { width: 100%; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-top: 20px; }
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
        <h4 class="text-center mb-4"><i class="fas fa-shield-alt"></i> Admin</h4>
        <a href="administrador.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="usuarios.php" class="active"><i class="fas fa-users"></i> Usuarios</a>
        <a href="horas.php"><i class="fas fa-clock"></i> Horas</a>
        <a href="pagos.php"><i class="fas fa-money-bill"></i> Pagos</a>
        <a href="unidades_habitacionales.php"><i class="fas fa-home"></i> Unidades Habitacionales</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </div>

    <!-- Contenido principal -->
    <div class="main-content" id="mainContent">
        <div class="top-navbar">
            <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            <div class="user-info">Administrador</div>
        </div>

        <!-- Título debajo del header -->
        <h1 class="page-title">Gestión de Usuarios</h1>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['usr_name']) ?></td>
                            <td><?= htmlspecialchars($row['usr_email']) ?></td>
                            <td>
                                <?php 
                                    if ($row['validado'] == 0) echo "Pendiente";
                                    elseif ($row['validado'] == 1) echo "Aprobado";
                                    else echo "Rechazado";
                                ?>
                            </td>
                            <td>
                                <a href="?aprobar=<?= $row['id'] ?>" class="btn-approve"><i class="fas fa-check"></i> Aprobar</a>
                                <a href="?rechazar=<?= $row['id'] ?>" class="btn-reject"><i class="fas fa-times"></i> Rechazar</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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
