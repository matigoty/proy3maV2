<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Front/login.html');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'] ?? "Usuario";

require 'config.php';

// Obtener datos actuales
$query_usuario = "SELECT * FROM usuario WHERE id = '$usuario_id'";
$result_usuario = mysqli_query($conn, $query_usuario);
$usuario = mysqli_fetch_assoc($result_usuario);

// Unidad habitacional
$query_unidad = "SELECT * FROM Unidad_habitacional WHERE usuario_id = '$usuario_id'";
$result_unidad = mysqli_query($conn, $query_unidad);
$unidad = mysqli_fetch_assoc($result_unidad);

// Últimos pagos
$query_pagos = "SELECT * FROM pagos WHERE usuario_id = '$usuario_id' ORDER BY id DESC LIMIT 5";
$result_pagos = mysqli_query($conn, $query_pagos);
$pagos = mysqli_fetch_all($result_pagos, MYSQLI_ASSOC);

$imagen_perfil = 'archivos/default.png';
if (!empty($usuario['imagen']) && file_exists(__DIR__ . '/' . $usuario['imagen'])) {
    $imagen_perfil = $usuario['imagen'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil Usuario</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
body { font-family:'Poppins',sans-serif; background:#f8f9fa; margin:0; }
.sidebar { position:fixed; top:0; left:-280px; width:280px; height:100vh;
background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); transition:all 0.3s ease; z-index:1000; overflow-y:auto; }
.sidebar.active { left:0; }
.sidebar-header { padding:20px; text-align:center; border-bottom:1px solid rgba(255,255,255,0.1); }
.sidebar-brand { color:white; font-size:1.5rem; font-weight:700; text-decoration:none; }
.nav-link { display:flex; align-items:center; padding:12px 20px; color:rgba(255,255,255,0.8); text-decoration:none;
transition:all 0.3s ease; border-left:3px solid transparent; }
.nav-link:hover, .nav-link.active { background:rgba(255,255,255,0.1); color:white; border-left-color:white; }
.nav-link i { width:20px; margin-right:10px; }

.main-content { margin-left:0; transition:all 0.3s ease; min-height:100vh; padding:20px; width:100%; }
.main-content.sidebar-open { margin-left:280px; }
.top-navbar { background:white; padding:15px 20px; box-shadow:0 2px 10px rgba(0,0,0,0.1);
display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; border-radius:10px; position:relative; }
.menu-toggle { background:none; border:none; font-size:1.2rem; color:#667eea; cursor:pointer; }
.user-info { font-weight:500; color:#333; }

.alert-banner { display:none; align-items:center; justify-content:center; gap:10px;
padding:10px 20px; border-radius:10px; font-weight:500; color:white;
position:fixed; top:15px; left:50%; transform:translateX(-50%); z-index:10000; }
.alert-success { background:linear-gradient(135deg,#28a745,#218838); }
.alert-error { background:linear-gradient(135deg,#dc3545,#b02a37); }

.card-form { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white;
border-radius:15px; padding:30px; max-width:700px; margin:auto; transition:transform 0.3s, box-shadow 0.3s; }
.card-form:hover { transform:translateY(-5px); box-shadow:0 10px 20px rgba(0,0,0,0.2); }

.form-label { font-weight:500; color:white; }
.form-control, .form-select { border-radius:8px; }
.btn-submit { background:#28a745; color:white; border:none; padding:10px 20px; border-radius:8px; width:100%;
margin-top:15px; transition:background 0.3s; }
.btn-submit:hover { background:#218838; }

.img-profile { width:120px; height:120px; border-radius:50%; object-fit:cover; margin-bottom:15px; border:3px solid white; }
.table-pagos { background:white; color:#333; border-radius:8px; overflow:hidden; }
.table-pagos th { background:#667eea; color:white; }
.table-pagos td, .table-pagos th { padding:8px 12px; text-align:center; }
</style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-brand"><i class="fas fa-user-clock"></i> Usuario</a>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-item"><a href="panel_usuario.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-item"><a href="registrar_horas.php" class="nav-link"><i class="fas fa-clock"></i> Registrar Horas</a></div>
        <div class="nav-item"><a href="registrar_pago.php" class="nav-link"><i class="fas fa-dollar-sign"></i> Registrar Pago</a></div>
        <div class="nav-item"><a href="perfil.php" class="nav-link active"><i class="fas fa-user-cog"></i> Gestionar Cuenta</a></div>
        <div class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></div>
    </nav>
</div>

<div class="main-content" id="mainContent">
    <div class="top-navbar">
        <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
        <div class="user-info"><?= htmlspecialchars($usuario_nombre) ?></div>
    </div>

    <h1 class="page-title text-center">Perfil de Usuario</h1>

    <div class="card-form text-center">
        <img src="<?= htmlspecialchars($imagen_perfil) ?>" alt="Foto Perfil" class="img-profile">

        <form id="formPerfil" enctype="multipart/form-data">
            <div class="mb-3 text-start">
                <label class="form-label">Nombre</label>
                <input type="text" name="usr_name" id="usr_name" class="form-control"
                    value="<?= htmlspecialchars($usuario['usr_name']) ?>" required minlength="3" maxlength="25">
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Email</label>
                <input type="email" name="usr_email" id="usr_email" class="form-control"
                    value="<?= htmlspecialchars($usuario['usr_email']) ?>" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Foto de Perfil</label>
                <input type="file" name="imagen" id="usr_image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Unidad Habitacional</label>
                <input type="text" class="form-control"
                    value="<?= $unidad ? htmlspecialchars($unidad['numPuerta']) : 'No asignada' ?>" readonly>
            </div>
            <button type="submit" class="btn-submit">Actualizar Datos</button>
        </form>

        <h4 class="mt-4">Últimos Pagos</h4>
        <table class="table table-pagos mt-2">
            <thead>
                <tr><th>Mes</th><th>Tipo</th><th>Estado de Pago</th><th>Estado de Cuenta</th></tr>
            </thead>
            <tbody>
            <?php if ($pagos): foreach($pagos as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['mes_pago']) ?></td>
                    <td><?= ucfirst($p['tipo']) ?></td>
                    <td><?= $p['validado']==0?'Pendiente':($p['validado']==1?'Aprobado':'Rechazado') ?></td>
                    <td><?= $p['estado_cuenta'] ? ucfirst($p['estado_cuenta']) : '-' ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="4">No hay pagos registrados</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="alerta" class="alert-banner"></div>

<script>
document.getElementById('menuToggle').addEventListener('click',()=>{
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('mainContent').classList.toggle('sidebar-open');
});

const form=document.getElementById('formPerfil');
const alerta=document.getElementById('alerta');
function showAlert(msg,tipo){
    alerta.textContent=msg;
    alerta.className=tipo==='success'?'alert-banner alert-success':'alert-banner alert-error';
    alerta.style.display='flex';
    setTimeout(()=>alerta.style.display='none',4000);
}

form.addEventListener('submit',async(e)=>{
    e.preventDefault();
    const formData=new FormData(form);
    const res=await fetch('http://localhost/proy3maV2/proy3maV2/API_cooperativa/api.php/perfil',{
        method:'POST',
        body:formData
    });
    let result=null;
    try{ result=await res.json(); }catch(err){ console.error("Error al parsear JSON",err); }
    console.log("Respuesta:",result);
    if(result?.success) showAlert(result.message,'success');
    else showAlert(result?.error||'Error al actualizar perfil','error');
});
</script>
</body>
</html>
