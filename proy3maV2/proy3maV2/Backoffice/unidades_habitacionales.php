<?php
session_start();

// Validar que el admin esté logueado
if (!isset($_SESSION['adm_id'])) {
    header("Location: index.php");
    exit;
}

require 'config.php'; // Conexión a la base de datos

// Manejar asignación/desasignación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unidad_id'], $_POST['accion'])) {
    $unidad_id = intval($_POST['unidad_id']);
    $accion = $_POST['accion'];

    if ($accion === 'asignar' && isset($_POST['usuario_id'])) {
        $usuario_id = intval($_POST['usuario_id']);
        $sql = "UPDATE Unidad_habitacional SET usuario_id = $usuario_id, estado='ocupada' WHERE id_unidad = $unidad_id";
    } elseif ($accion === 'desasignar') {
        $sql = "UPDATE Unidad_habitacional SET usuario_id = NULL, estado='desocupada' WHERE id_unidad = $unidad_id";
    }
    mysqli_query($conn, $sql);
}

// Obtener unidades con usuarios asignados
$unidades = mysqli_query($conn, "
    SELECT u.id_unidad, u.numPuerta, u.estado, u.usuario_id, us.usr_name
    FROM Unidad_habitacional u
    LEFT JOIN usuario us ON u.usuario_id = us.id
    ORDER BY u.id_unidad ASC
");

// Obtener usuarios disponibles (solo los que tienen pago inicial aprobado)
$usuarios_disponibles = mysqli_query($conn, "
    SELECT id, usr_name
    FROM usuario
    WHERE id NOT IN (
        SELECT usuario_id 
        FROM Unidad_habitacional 
        WHERE usuario_id IS NOT NULL
    )
    AND id IN (
        SELECT usuario_id
        FROM pagos
        WHERE tipo='inicial' AND validado=1
    )
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Unidades Habitacionales - Backoffice</title>
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
        color: white; padding-top: 20px; transition: all 0.3s ease; z-index: 1000;
        overflow-y: auto;
    }
    .sidebar.active { left: 0; }
    .sidebar a {
        display: flex; align-items: center; padding: 12px 20px; color: rgba(255,255,255,0.8); text-decoration: none;
        transition: all 0.3s ease; border-left: 3px solid transparent;
    }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: white; border-left-color: white; }
    .sidebar a i { width: 20px; margin-right: 10px; }
    .main-content { margin-left: 0; transition: all 0.3s ease; min-height: 100vh; padding: 20px; width: 100%; }
    .main-content.sidebar-open { margin-left: 280px; }
    .top-navbar { background: white; padding: 15px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between; border-radius: 10px; margin-bottom: 20px; }
    .menu-toggle { font-size: 22px; cursor: pointer; color: #667eea; border: none; background: none; }
    .page-title { font-size: 2rem; font-weight: 600; color: white; margin-bottom: 20px; }
    table { background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); overflow: hidden; }
    thead { background: #667eea; color: white; }
    th, td { vertical-align: middle !important; text-align: center; }
    tbody tr:hover { background: rgba(0,0,0,0.03); }
    .btn-assign { background: #28a745; color: white; border-radius: 8px; padding: 5px 10px; border: none; }
    .btn-unassign { background: #dc3545; color: white; border-radius: 8px; padding: 5px 10px; border: none; }
    select.form-select-sm { max-width: 180px; }
    .badge { font-size: 0.9rem; padding: 5px 10px; border-radius: 5px; }
</style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
    
        <a href="administrador.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="usuarios.php"><i class="fas fa-users"></i> Usuarios</a>
        <a href="horas.php"><i class="fas fa-clock"></i> Horas</a>
        <a href="pagos.php"><i class="fas fa-money-bill"></i> Pagos</a>
        <a href="unidades_habitacionales.php" class="active"><i class="fas fa-home"></i> Unidades Habitacionales</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </div>

    <!-- Contenido principal -->
    <div class="main-content" id="mainContent">
        <div class="top-navbar">
            <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            <div class="user-info" style="color:#764ba2;">Administrador: <?= $_SESSION['adm_name'] ?? 'Admin' ?></div>
        </div>

        <h1 class="page-title">Unidades Habitacionales</h1>

        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>ID Unidad</th>
                    <th>Puerta</th>
                    <th>Estado</th>
                    <th>Usuario Asignado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = mysqli_fetch_assoc($unidades)): ?>
                <tr>
                    <td><?= $row['id_unidad'] ?></td>
                    <td><?= htmlspecialchars($row['numPuerta']) ?></td>
                    <td>
                        <span class="badge <?= $row['estado']=='ocupada'?'bg-danger':'bg-success' ?>">
                            <?= $row['estado'] ?>
                        </span>
                    </td>
                    <td><?= $row['usr_name'] ?? '---' ?></td>
                    <td>
                        <?php if ($row['usuario_id']): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="unidad_id" value="<?= $row['id_unidad'] ?>">
                                <input type="hidden" name="accion" value="desasignar">
                                <button type="submit" class="btn-unassign">
                                    <i class="fas fa-times"></i> Desasignar
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="post" style="display:flex; gap:5px; justify-content:center;">
                                <input type="hidden" name="unidad_id" value="<?= $row['id_unidad'] ?>">
                                <input type="hidden" name="accion" value="asignar">
                                <select name="usuario_id" class="form-select form-select-sm" required>
                                    <option value="">Seleccionar usuario</option>
                                    <?php
                                    mysqli_data_seek($usuarios_disponibles, 0);
                                    while($u = mysqli_fetch_assoc($usuarios_disponibles)):
                                    ?>
                                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['usr_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <button type="submit" class="btn-assign">
                                    <i class="fas fa-check"></i> Asignar
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
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
