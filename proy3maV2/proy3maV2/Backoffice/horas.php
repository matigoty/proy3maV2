<?php
session_start();
// Validar que el admin esté logueado
if (!isset($_SESSION['adm_id'])) {
    header("Location: index.php");
    exit;
}
require_once __DIR__ . "/config.php";

// Procesar acción de aprobación/rechazo
if (isset($_GET['accion'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $accion = $_GET['accion'];

    if (in_array($accion, ['aprobar','rechazar'])) {
        $estado = $accion === 'aprobar' ? 'aprobada' : 'rechazada';
        $stmt = $conn->prepare("UPDATE horas_semanales SET estado_validacion=? WHERE id=?");
        $stmt->bind_param("si", $estado, $id);
        $stmt->execute();
    }
}

// Traer registros de horas en orden de subida (más reciente primero)
$result = $conn->query("
    SELECT h.id, u.usr_name, h.fecha, h.total_horas, h.justificacion, h.tipo_solicitud, h.comprobante, h.estado_validacion
    FROM horas_semanales h
    JOIN usuario u ON h.usuario_id = u.id
    ORDER BY h.id DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Horas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            margin:0; 
            min-height: 100vh; 
        }
        .sidebar {
            position: fixed; top: 0; left: -280px; width: 280px; height: 100vh; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            transition: all 0.3s ease; z-index: 1000; overflow-y: auto; 
        }
        .sidebar.active { left: 0; }
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand { color: white; font-size: 1.5rem; font-weight: 700; text-decoration: none; }
        .nav-link { display: flex; align-items: center; padding: 12px 20px; color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s ease; border-left: 3px solid transparent; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; border-left-color: white; }
        .nav-link i { width: 20px; margin-right: 10px; }
        .main-content { margin-left: 0; transition: all 0.3s ease; min-height: 100vh; padding: 20px; width: 100%; }
        .main-content.sidebar-open { margin-left: 280px; } 
        .top-navbar { background: white; padding: 15px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-radius: 10px; }
        .menu-toggle { background: none; border: none; font-size: 1.2rem; color: #667eea; cursor: pointer; }
        .page-title { font-size: 2rem; font-weight: 600; color: white; margin-bottom: 20px; }
        .card { width: 100%; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        table th { background: #667eea; color: white; border: none; text-align: center; }
        table td, table th { vertical-align: middle; text-align: center; }
        .btn-approve { background: #28a745; color: white; border-radius: 5px; padding: 5px 10px; margin-right: 5px; text-decoration: none; display:inline-block; }
        .btn-reject { background: #dc3545; color: white; border-radius: 5px; padding: 5px 10px; text-decoration: none; display:inline-block; }
        img { border: 1px solid #888; border-radius: 5px; max-width:100px; max-height:100px; }
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
            <a href="#" class="sidebar-brand"><i class="fas fa-shield-alt"></i> Admin Cooperativa</a>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-item"><a href="administrador.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
            <div class="nav-item"><a href="usuarios.php" class="nav-link"><i class="fas fa-users"></i> Usuarios</a></div>
            <div class="nav-item"><a href="unidades_habitacionales.php" class="nav-link"><i class="fas fa-home"></i> Unidades Habitacionales</a></div>
            <div class="nav-item"><a href="pagos.php" class="nav-link"><i class="fas fa-money-bill"></i> Pagos</a></div>
            <div class="nav-item"><a href="horas.php" class="nav-link active"><i class="fas fa-clock"></i> Horas</a></div>
            <div class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></div>
        </nav>
    </div>

    <div class="main-content" id="mainContent">
        <div class="top-navbar">
            <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            <div class="user-info"><span style="color:#764b2;">Administrador</span></div>
        </div>

        <h1 class="page-title">Gestión de Horas y Justificaciones</h1>
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Semana</th>
                            <th>Horas</th>
                            <th>Justificación</th>
                            <th>Solicitud</th>
                            <th>Comprobante</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['usr_name']) ?></td>
                            <td><?= htmlspecialchars($row['fecha']) ?></td>
                            <td style="color: <?= $row['total_horas'] >= 21 ? 'green' : 'red' ?>;">
                                <?= htmlspecialchars($row['total_horas']) ?>
                            </td>
                            <td><?= nl2br(htmlspecialchars($row['justificacion'])) ?></td>
                            <td><?= htmlspecialchars($row['tipo_solicitud']) ?></td>
                            <td>
                                <?php 
                                    $ruta_web = "../API_cooperativa/" . $row['comprobante'];
                                    $ruta_fisica = __DIR__ . "/../API_cooperativa/" . $row['comprobante'];
                                ?>
                                <?php if (!empty($row['comprobante']) && file_exists($ruta_fisica)): ?>
                                    <a href="<?= $ruta_web ?>" target="_blank">
                                        <img src="<?= $ruta_web ?>" alt="Comprobante">
                                    </a>
                                <?php else: ?>
                                    No hay comprobante
                                <?php endif; ?>
                            </td>
                            <td><?= ucfirst($row['estado_validacion']) ?></td>
                            <td>
                                <a class="btn-approve" href="?accion=aprobar&id=<?= $row['id'] ?>"><i class="fas fa-check"></i> Aprobar</a>
                                <a class="btn-reject" href="?accion=rechazar&id=<?= $row['id'] ?>"><i class="fas fa-times"></i> Rechazar</a>
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
