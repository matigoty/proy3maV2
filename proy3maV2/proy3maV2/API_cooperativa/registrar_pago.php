<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Front/login.html');
    exit;
}
$usuario_nombre = $_SESSION['usuario_nombre'] ?? "Usuario";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrar Pago</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
body { font-family:'Poppins',sans-serif; background:#f8f9fa; margin:0; }
.sidebar { position:fixed; top:0; left:-280px; width:280px; height:100vh; background:linear-gradient(135deg,#667eea,#764ba2); transition:all 0.3s ease; overflow-y:auto; z-index:1000; }
.sidebar.active { left:0; }
.sidebar-header { padding:20px; text-align:center; border-bottom:1px solid rgba(255,255,255,0.1); }
.sidebar-brand { color:white; font-size:1.5rem; font-weight:700; text-decoration:none; }
.nav-link { display:flex; align-items:center; padding:12px 20px; color:rgba(255,255,255,0.8); text-decoration:none; border-left:3px solid transparent; transition:all 0.3s ease; }
.nav-link:hover, .nav-link.active { background:rgba(255,255,255,0.1); color:white; border-left-color:white; }
.nav-link i { width:20px; margin-right:10px; }
.main-content { margin-left:0; transition:all 0.3s ease; min-height:100vh; padding:20px; }
.main-content.sidebar-open { margin-left:280px; }
.top-navbar { background:white; padding:15px 20px; box-shadow:0 2px 10px rgba(0,0,0,0.1); display:flex; align-items:center; justify-content:space-between; border-radius:10px; margin-bottom:20px; }
.menu-toggle { background:none; border:none; font-size:1.2rem; color:#667eea; cursor:pointer; }
.user-info { font-weight:500; color:#333; }
.page-title { font-size:2rem; font-weight:600; text-align:center; color:#333; }
.card-form { background:linear-gradient(135deg,#667eea,#764ba2); color:white; border-radius:15px; padding:30px; max-width:600px; margin:auto; }
.btn-submit { background:#28a745; color:white; border:none; padding:10px; border-radius:8px; width:100%; margin-top:15px; transition:background 0.3s; }
.btn-submit:hover { background:#218838; }
.info-box { background:rgba(255,255,255,0.15); border-radius:10px; padding:10px; margin-bottom:15px; font-size:0.9rem; text-align:center; }
.alert-banner { display:none; align-items:center; justify-content:center; gap:10px; padding:10px 20px; border-radius:10px; font-weight:500; color:white; position:fixed; top:15px; left:50%; transform:translateX(-50%); z-index:10000; }
.alert-success { background:linear-gradient(135deg,#28a745,#218838); display:flex; }
.alert-error { background:linear-gradient(135deg,#dc3545,#b02a37); display:flex; }
</style>
</head>
<body>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header"><a href="#" class="sidebar-brand"><i class="fas fa-user-clock"></i> Usuario</a></div>
    <nav class="sidebar-nav">
        <div class="nav-item"><a href="panel_usuario.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-item"><a href="registrar_horas.php" class="nav-link"><i class="fas fa-clock"></i> Registrar Horas</a></div>
        <div class="nav-item"><a href="registrar_pago.php" class="nav-link active"><i class="fas fa-dollar-sign"></i> Registrar Pago</a></div>
        <div class="nav-item"><a href="perfil.php" class="nav-link"><i class="fas fa-user-cog"></i> Gestionar Cuenta</a></div>
        <div class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></div>
    </nav>
</div>

<div class="main-content" id="mainContent">
    <div class="top-navbar">
        <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
        <div class="user-info"><?= htmlspecialchars($usuario_nombre) ?></div>
    </div>

    <h1 class="page-title">Registrar Pago</h1>
    <div class="card-form">
        <div class="info-box" id="infoBox">
            <i class="fas fa-info-circle"></i> Verificando tu estado...
        </div>

        <form id="formPago" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Comprobante</label>
                <input type="file" name="comprobante" id="comprobante" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mes del Pago</label>
                <input type="month" name="mes_pago" id="mes_pago" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo de Pago</label>
                <select name="tipo_pago" id="tipo_pago" class="form-select" required>
                    <option value="inicial">Inicial</option>
                    <option value="mensual">Mensual</option>
                </select>
            </div>
            <button type="submit" class="btn-submit">Subir Comprobante</button>
        </form>
    </div>
</div>

<div id="alerta" class="alert-banner"></div>

<script>
document.getElementById('menuToggle').addEventListener('click',()=> {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('mainContent').classList.toggle('sidebar-open');
});

const form = document.getElementById('formPago');
const tipoPago = document.getElementById('tipo_pago');
const infoBox = document.getElementById('infoBox');
const alerta = document.getElementById('alerta');

function showAlert(msg, tipo){
    alerta.textContent = msg;
    alerta.className = tipo==='success'?'alert-banner alert-success':'alert-banner alert-error';
    alerta.style.display='flex';
    setTimeout(()=>alerta.style.display='none',4000);
}

// Verificar estado del usuario
async function verificarUnidad(){
    const res = await fetch(`${window.location.origin}/proy3maV2/proy3maV2/API_cooperativa/api.php/usuario_unidad`);
    const data = await res.json();
    const tieneUnidad = data?.tiene_unidad;

    if (tieneUnidad) {
        tipoPago.querySelector('option[value="inicial"]').disabled = true;
        infoBox.innerHTML = `<i class="fas fa-info-circle"></i> Tienes una unidad asignada. Solo puedes subir pagos <b>mensuales</b>.`;
    } else {
        tipoPago.querySelector('option[value="mensual"]').disabled = true;
        infoBox.innerHTML = `<i class="fas fa-info-circle"></i> Aún no tienes unidad asignada. Solo puedes subir un pago <b>inicial</b>.`;
    }
}

verificarUnidad();

// Enviar formulario
form.addEventListener('submit', async e=>{
    e.preventDefault();
    const formData = new FormData(form);
    const res = await fetch(`${window.location.origin}/proy3maV2/proy3maV2/API_cooperativa/api.php/pagos`, {
        method:'POST', body:formData
    });
    let result=null;
    try{result=await res.json();}catch(e){console.error(e);}
    console.log('Respuesta de la API:', result, 'Status:', res.status);
    if(result?.success) showAlert(result.message,'success');
    else showAlert(result?.error || 'Error al registrar pago','error');
});
</script>
</body>
</html>
