<?php
session_start();

// Validar sesión del administrador
if (!isset($_SESSION['adm_id'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . "/config.php";

// PROCESAR ACCIONES DE ADMIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pago_id'])) {
    $pago_id = intval($_POST['pago_id']);

    // Aprobar o rechazar comprobante
    if (isset($_POST['estado'])) {
        $estado = intval($_POST['estado']);
        if (in_array($estado, [1, 2])) {
            $stmt = $conn->prepare("UPDATE pagos SET validado = ? WHERE id = ?");
            $stmt->bind_param("ii", $estado, $pago_id);
            $stmt->execute();
        }
    }

    // Marcar estado de cuenta (al día / atrasado)
    if (isset($_POST['estado_cuenta'])) {
        $estadoCuenta = $_POST['estado_cuenta'] === 'atrasado' ? 'atrasado' : 'al_dia';
        $stmt = $conn->prepare("UPDATE pagos SET estado_cuenta = ? WHERE id = ?");
        $stmt->bind_param("si", $estadoCuenta, $pago_id);
        $stmt->execute();
    }

    header("Location: pagos.php");
    exit;
}

// --- CONSULTAR PAGOS ---
$result = $conn->query("
    SELECT 
        p.id, 
        u.usr_name, 
        p.tipo, 
        p.imagen, 
        p.validado, 
        p.mes_pago, 
        p.estado_cuenta
    FROM pagos p
    JOIN usuario u ON u.id = p.usuario_id
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Pagos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            background-attachment: fixed; 
            margin:0; 
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
        .top-navbar { background: white; padding: 15px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-radius: 10px; }
        .menu-toggle { background: none; border: none; font-size: 1.2rem; color: #667eea; cursor: pointer; }
        .user-info span { color: #764ba2; font-weight: 600; }
        .page-title { font-size: 2rem; font-weight: 600; color: white; margin-bottom: 20px; }
        .card { width: 100%; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        table th { background: #667eea; color: white; border: none; text-align: center; }
        table td, table th { vertical-align: middle; text-align: center; }
        .btn-approve { background: #28a745; color: white; border-radius: 5px; padding: 5px 10px; margin: 2px; border: none; }
        .btn-reject { background: #dc3545; color: white; border-radius: 5px; padding: 5px 10px; margin: 2px; border: none; }
        .btn-status { background: #17a2b8; color: white; border-radius: 5px; padding: 5px 10px; margin: 2px; border: none; }
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
            <div class="nav-item"><a href="pagos.php" class="nav-link active"><i class="fas fa-money-bill"></i> Pagos</a></div>
            <div class="nav-item"><a href="horas.php" class="nav-link"><i class="fas fa-clock"></i> Horas</a></div>
            <div class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></div>
        </nav>
    </div>

    <div class="main-content" id="mainContent">
        <div class="top-navbar">
            <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            <div class="user-info"><span>Administrador</span></div>
        </div>

        <h1 class="page-title">Comprobantes de Pago</h1>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Tipo</th>
                            <th>Mes de Pago</th>
                            <th>Imagen</th>
                            <th>Estado</th>
                            <th>Cuenta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['usr_name']) ?></td>
                            <td><?= ucfirst($row['tipo']) ?></td>
                            <td><?= htmlspecialchars($row['mes_pago']) ?></td>
                            <td>
                                <?php 
                                    $ruta_web = "../API_cooperativa/" . $row['imagen'];
                                    $ruta_fisica = __DIR__ . "/../API_cooperativa/" . $row['imagen'];
                                ?>
                                <?php if (!empty($row['imagen']) && file_exists($ruta_fisica)): ?>
                                    <a href="<?= $ruta_web ?>" target="_blank">
                                        <img src="<?= $ruta_web ?>" alt="Comprobante">
                                    </a>
                                <?php else: ?>
                                    No hay imagen
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $row['validado'] == 0 ? "Pendiente" : ($row['validado'] == 1 ? "Aprobado" : "Rechazado") ?>
                            </td>
                            <td>
                                <?= $row['estado_cuenta'] == 'al_dia' ? 'Al día' : 'Atrasado' ?>
                            </td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="pago_id" value="<?= $row['id'] ?>">
                                    <button name="estado" value="1" class="btn-approve"><i class="fas fa-check"></i></button>
                                    <button name="estado" value="2" class="btn-reject"><i class="fas fa-times"></i></button>
                                    <button name="estado_cuenta" value="al_dia" class="btn-status"><i class="fas fa-sun"></i> Al día</button>
                                    <button name="estado_cuenta" value="atrasado" class="btn-status" style="background:#ff9800;"><i class="fas fa-exclamation-triangle"></i> Atrasado</button>
                                </form>
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
</body>
</html>
