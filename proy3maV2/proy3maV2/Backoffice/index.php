<?php
session_start();
require_once __DIR__ . "/config.php";

class Administrador
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function loginAdministrador($adm_email, $adm_pass)
    {
        $query = "SELECT * FROM administradores WHERE adm_email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $adm_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $administrador = $result->fetch_assoc();
            if (password_verify($adm_pass, $administrador['adm_pass'])) {
                return $administrador;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

$administrador = new Administrador($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adm_email = $_POST['adm_email'] ?? null;
    $adm_pass = $_POST['adm_pass'] ?? null;

    if ($adm_email && $adm_pass) {
        $admin = $administrador->loginAdministrador($adm_email, $adm_pass);
        if ($admin) {
            $_SESSION['adm_id'] = $admin['id'];
            $_SESSION['adm_name'] = $admin['adm_name'];
            header("Location: administrador.php");
            exit;
        } else {
            $error = "Correo o contraseña incorrectos";
        }
    } else {
        $error = "Faltan datos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador - Tekoá</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }
        .floating-elements {
            position: absolute; width: 100%; height: 100%; overflow: hidden; z-index: 0;
        }
        .floating-element {
            position: absolute; opacity: 0.1; animation: float 6s ease-in-out infinite; color: white;
        }
        .floating-element:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
        .floating-element:nth-child(2) { top: 60%; right: 10%; animation-delay: 2s; }
        .floating-element:nth-child(3) { bottom: 20%; left: 20%; animation-delay: 4s; }
        .floating-element:nth-child(4) { top: 30%; right: 30%; animation-delay: 1s; }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }
        .login-container { position: relative; z-index: 2; width: 100%; max-width: 500px; margin: 0 auto; }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 60px rgba(0, 0, 0, 0.3);
        }
        .brand-logo { text-align: center; margin-bottom: 2rem; }
        .brand-logo i { font-size: 3rem; color: #667eea; margin-bottom: 1rem; }
        .brand-title { font-size: 2rem; font-weight: 700; color: #333; margin-bottom: 0.5rem; }
        .brand-subtitle { color: #666; font-size: 0.9rem; margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.5rem; position: relative; }
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            padding: 15px 20px 15px 50px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
        }
        .form-icon {
            position: absolute; left: 18px; top: 50%;
            transform: translateY(-50%);
            color: #667eea; font-size: 1.1rem;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none; border-radius: 15px;
            padding: 15px; font-weight: 600; font-size: 1.1rem;
            color: white; width: 100%; transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        .btn-login:active { transform: translateY(0); }
        .alert { border-radius: 15px; border: none; padding: 15px; margin-bottom: 1.5rem; }
        .alert-success { background: rgba(40, 167, 69, 0.1); color: #28a745; border-left: 4px solid #28a745; }
        .alert-danger { background: rgba(220, 53, 69, 0.1); color: #dc3545; border-left: 4px solid #dc3545; }
        @media (max-width: 768px) {
            .login-card { margin: 1rem; padding: 2rem; }
            .brand-title { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="floating-elements">
        <div class="floating-element"><i class="fas fa-user-shield" style="font-size: 4rem;"></i></div>
        <div class="floating-element"><i class="fas fa-key" style="font-size: 3.5rem;"></i></div>
        <div class="floating-element"><i class="fas fa-lock" style="font-size: 4.5rem;"></i></div>
        <div class="floating-element"><i class="fas fa-building" style="font-size: 3rem;"></i></div>
    </div>

    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="brand-logo">
                    <i class="fas fa-user-shield"></i>
                    <h1 class="brand-title">Administrador</h1>
                    <p class="brand-subtitle">Panel de Control</p>
                </div>

                <!-- Alert container -->
                <div id="alertContainer">
                    <?php if(!empty($error)) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>
                </div>

                <form action="" method="post">
                    <div class="form-group">
                        <i class="fas fa-envelope form-icon"></i>
                        <input type="email" class="form-control" id="adm_email" name="adm_email" placeholder="Correo electrónico" required>
                    </div>
                    <div class="form-group">
                        <i class="fas fa-lock form-icon"></i>
                        <input type="password" class="form-control" id="adm_pass" name="adm_pass" placeholder="Contraseña" required>
                    </div>
                    <button type="submit" class="btn btn-login" id="loginBtn">
                        <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                    </button>
                </form>



                <div class="back-link">
                    <a href="../Front/index.php"><i class="fas fa-arrow-left me-1"></i> Volver al inicio</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
