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
<title>Registrar Horas</title>
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
.info-box { background:rgba(255,255,255,0.15); border-radius:10px; padding:10px; margin-bottom:15px; font-size:0.9rem; text-align:center; color:white; }
.card-form { background:linear-gradient(135deg,#667eea,#764ba2); color:white; border-radius:15px; padding:30px; max-width:600px; margin:auto; }
.btn-submit { background:#28a745; color:white; border:none; padding:10px; border-radius:8px; width:100%; margin-top:15px; transition:background 0.3s; }
.btn-submit:hover { background:#218838; }
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
        <div class="nav-item"><a href="registrar_horas.php" class="nav-link active"><i class="fas fa-clock"></i> Registrar Horas</a></div>
        <div class="nav-item"><a href="registrar_pago.php" class="nav-link"><i class="fas fa-dollar-sign"></i> Registrar Pago</a></div>
        <div class="nav-item"><a href="perfil.php" class="nav-link"><i class="fas fa-user-cog"></i> Gestionar Cuenta</a></div>
        <div class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></div>
    </nav>
</div>

<div class="main-content" id="mainContent">
    <div class="top-navbar">
        <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
        <div class="user-info"><?= htmlspecialchars($usuario_nombre) ?></div>
    </div>

    <h1 class="page-title">Registrar Horas</h1>

    <div class="card-form">
        <div class="info-box" id="infoBox">
            <i class="fas fa-info-circle"></i> Verificando si formas parte de una unidad habitacional...
        </div>

        <form id="formHoras" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Semana</label>
                <input type="date" name="fecha" id="fecha" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Total de horas trabajadas</label>
                <input type="number" name="total_horas" id="total_horas" class="form-control" required min="0" max="168">
            </div>
            <div class="mb-3">
                <label class="form-label">Justificación (si trabajaste menos de 21 h)</label>
                <textarea name="justificacion" id="justificacion" class="form-control" rows="2" disabled></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo de solicitud</label>
                <select name="tipo_solicitud" id="tipo_solicitud" class="form-select" disabled>
                    <option value="ninguna">Ninguna</option>
                    <option value="exoneracion">Exoneración</option>
                    <option value="pago_compensatorio">Pago compensatorio</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Comprobante (solo si es pago compensatorio)</label>
                <input type="file" name="comprobante" id="comprobante" class="form-control" accept=".jpg,.jpeg,.png,.pdf" disabled>
            </div>
            <button type="submit" class="btn-submit">Guardar</button>
        </form>
    </div>
</div>

<div id="alerta" class="alert-banner"></div>

<script>
document.getElementById('menuToggle').addEventListener('click',()=> {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('mainContent').classList.toggle('sidebar-open');
});

const form = document.getElementById('formHoras');
const tipoSolicitud = document.getElementById('tipo_solicitud');
const comprobante = document.getElementById('comprobante');
const justificacion = document.getElementById('justificacion');
const totalHoras = document.getElementById('total_horas');
const infoBox = document.getElementById('infoBox');
const alerta = document.getElementById('alerta');

function showAlert(msg,tipo){
    alerta.textContent=msg;
    alerta.className=tipo==='success'?'alert-banner alert-success':'alert-banner alert-error';
    alerta.style.display='flex';
    setTimeout(()=>alerta.style.display='none',4000);
}

// Control de horas y habilitación de campos
totalHoras.addEventListener('input',()=>{
    const horas = parseInt(totalHoras.value);
    if(horas < 21){
        justificacion.disabled = false;
        justificacion.required = true;
        tipoSolicitud.disabled = false;
    }else{
        justificacion.disabled = true;
        justificacion.required = false;
        tipoSolicitud.disabled = true;
        tipoSolicitud.value = 'ninguna';
        comprobante.disabled = true;
        comprobante.required = false;
        comprobante.value = '';
    }
});

// Habilitar comprobante si es pago compensatorio
tipoSolicitud.addEventListener('change',()=>{
    if(tipoSolicitud.value === 'pago_compensatorio'){
        comprobante.disabled = false;
        comprobante.required = true;
    } else {
        comprobante.disabled = true;
        comprobante.required = false;
        comprobante.value = '';
    }
});

// Verificar si el usuario pertenece a una unidad
async function verificarUnidad(){
    const res = await fetch(`${window.location.origin}/proy3maV2/proy3maV2/API_cooperativa/api.php/usuario_unidad`);
    const data = await res.json();
    if(!data.tiene_unidad){
        form.querySelectorAll('input, select, textarea, button').forEach(el=>el.disabled=true);
        infoBox.innerHTML = `<i class="fas fa-exclamation-triangle"></i> No formas parte de ninguna unidad habitacional. <br> Solo los socios con una unidad asignada pueden registrar sus horas trabajadas.`;
    }else{
        infoBox.innerHTML = `<i class="fas fa-info-circle"></i> Puedes registrar tus horas semanales porque perteneces a una unidad habitacional activa.`;
    }
}
verificarUnidad();

// Enviar formulario
form.addEventListener('submit', async e=>{
    e.preventDefault();
    const horas = parseInt(totalHoras.value);
    const tipo = tipoSolicitud.value;

    if (horas < 21) {
        if (!justificacion.value.trim()) return showAlert('Debes justificar si trabajaste menos de 21 h','error');
        if (tipo === 'ninguna') return showAlert('Selecciona un tipo de solicitud','error');
        if (tipo === 'pago_compensatorio' && comprobante.files.length === 0)
            return showAlert('Debes subir un comprobante','error');
    }

    const formData = new FormData();
    formData.append('fecha', document.getElementById('fecha').value);
    formData.append('total_horas', totalHoras.value);
    formData.append('justificacion', justificacion.value);
    formData.append('tipo_solicitud', tipoSolicitud.value);
    if (comprobante.files.length > 0) formData.append('comprobante', comprobante.files[0]);

    const res = await fetch(`${window.location.origin}/proy3maV2/proy3maV2/API_cooperativa/api.php/horas`,{
        method:'POST',
        body:formData
    });

    let result=null; 
    try{result=await res.json();}catch(e){console.error(e);}
    console.log('Respuesta API:', result);
    if(result?.success) showAlert(result.message,'success');
    else showAlert(result?.error || 'Error al registrar horas','error');
});
</script>
</body>
</html>
