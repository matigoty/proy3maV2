<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
session_start();

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['PATH_INFO'] ?? '/';

//////////////////////////////////////////////////////////////////////////////////
// GET /usuario_unidad
if ($method === 'GET' && $endpoint === '/usuario_unidad') {
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no autenticado']);
        exit;
    }

    $usuario_id = intval($_SESSION['usuario_id']);
    $stmt = $conn->prepare("SELECT id_unidad FROM Unidad_habitacional WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $tieneUnidad = ($res->num_rows > 0);

    echo json_encode(['tiene_unidad' => $tieneUnidad]);
    exit;
}

///////////////////////////////////////////////////////////////////////////////////
// POST /horas
if ($method === 'POST' && $endpoint === '/horas') {
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No hay usuario logueado']);
        exit;
    }

    $usuario_id = intval($_SESSION['usuario_id']);

    // debe tener unidad asignada
    $check = $conn->prepare("SELECT id_unidad FROM Unidad_habitacional WHERE usuario_id = ?");
    $check->bind_param("i", $usuario_id);
    $check->execute();
    $res = $check->get_result();
    $tiene_unidad = ($res->num_rows > 0);

    if (!$tiene_unidad) {
        http_response_code(403);
        echo json_encode(['error' => 'No puede registrar horas sin tener una unidad habitacional asignada.']);
        exit;
    }

    $fecha = $_POST['fecha'] ?? null;
    $total_horas = $_POST['total_horas'] ?? null;
    $justificacion = $_POST['justificacion'] ?? '';
    $tipo_solicitud = $_POST['tipo_solicitud'] ?? 'ninguna';
    $comprobante = null;

    if (!$fecha || !$total_horas) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos o vacíos']);
        exit;
    }

    if ($tipo_solicitud === 'pago_compensatorio' && isset($_FILES['comprobante'])) {
        $targetDir = __DIR__ . "/../uploads/compensatorios/";
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = uniqid() . "_" . basename($_FILES["comprobante"]["name"]);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["comprobante"]["tmp_name"], $targetFile)) {
            $comprobante = $fileName;
        }
    }

    $stmt = $conn->prepare("INSERT INTO horas_semanales (usuario_id, fecha, total_horas, justificacion, tipo_solicitud, comprobante) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isisss", $usuario_id, $fecha, $total_horas, $justificacion, $tipo_solicitud, $comprobante);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Horas registradas correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al registrar horas']);
    }
    exit;
}

///////////////////////////////////////////////////////////////////////////////////
// POST /pagos
if ($method === 'POST' && $endpoint === '/pagos') {
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no autenticado']);
        exit;
    }

    $usuario_id = intval($_SESSION['usuario_id']);
    $mes_pago = $_POST['mes_pago'] ?? '';
    $tipo_pago = $_POST['tipo_pago'] ?? '';
    $imagen = $_FILES['comprobante'] ?? null;

    if (!$mes_pago || !$tipo_pago || !$imagen) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos incompletos']);
        exit;
    }

    // Verificar unidad habitacional real
    $check = $conn->prepare("SELECT id_unidad FROM Unidad_habitacional WHERE usuario_id = ?");
    $check->bind_param("i", $usuario_id);
    $check->execute();
    $res = $check->get_result();
    $tiene_unidad = ($res->num_rows > 0);

    // Validaciones
    if ($tipo_pago === 'mensual' && !$tiene_unidad) {
        http_response_code(403);
        echo json_encode(['error' => 'No puede registrar pagos mensuales sin tener una unidad habitacional asignada.']);
        exit;
    }
    if ($tipo_pago === 'inicial' && $tiene_unidad) {
        http_response_code(403);
        echo json_encode(['error' => 'No puede registrar pagos iniciales si ya tiene una unidad asignada.']);
        exit;
    }

    $targetDir = __DIR__ . "/../uploads/pagos/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
    $fileName = time() . "_" . basename($imagen["name"]);
    $targetFile = $targetDir . $fileName;
    if (!move_uploaded_file($imagen["tmp_name"], $targetFile)) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al subir el comprobante.']);
        exit;
    }

    list($anio_pago, $mes_pago_num) = explode("-", $mes_pago);
    $anio_pago = intval($anio_pago);
    $mes_pago_num = intval($mes_pago_num);
    $anio_actual = intval(date("Y"));
    $mes_actual = intval(date("n"));
    $estado_cuenta = ($anio_pago < $anio_actual || ($anio_pago == $anio_actual && $mes_pago_num < $mes_actual))
        ? 'atrasado' : 'al_dia';

    $stmt = $conn->prepare("INSERT INTO pagos (usuario_id, mes_pago, tipo, imagen, validado, estado_cuenta) VALUES (?, ?, ?, ?, 0, ?)");
    $stmt->bind_param("issss", $usuario_id, $mes_pago, $tipo_pago, $fileName, $estado_cuenta);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Pago registrado correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar el pago']);
    }
    exit;
}
///////////////////////////////////////////////////////////////////////////////////

// POST /perfil
if ($method === 'POST' && $endpoint === '/perfil') {
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no autenticado']);
        exit;
    }

    $usuario_id = intval($_SESSION['usuario_id']);
    $nombre = $_POST['usr_name'] ?? '';
    $email = $_POST['usr_email'] ?? '';
    $imagen = $_FILES['imagen'] ?? null;

    if (!$nombre || !$email) {
        http_response_code(400);
        echo json_encode(['error' => 'El nombre y el correo son obligatorios.']);
        exit;
    }

    // Obtener datos actuales del usuario
    $stmt = $conn->prepare("SELECT usr_email, imagen FROM usuario WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $actual = $res->fetch_assoc();

    if (!$actual) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado.']);
        exit;
    }

    // Verificar si el email ya está en uso por otro usuario
    $stmt = $conn->prepare("SELECT id FROM usuario WHERE usr_email = ? AND id != ?");
    $stmt->bind_param("si", $email, $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'El correo electrónico ya está registrado por otro usuario.']);
        exit;
    }

    // Mantener imagen actual o actualizarla si se subió una nueva
    $nueva_imagen = $actual['imagen'];
    if ($imagen && $imagen['error'] === 0) {
        $targetDir = __DIR__ . "/../uploads/perfil/";
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($imagen["name"]);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($imagen["tmp_name"], $targetFile)) {
            $nueva_imagen = "uploads/perfil/" . $fileName;
        }
    }

    // Actualizar datos del usuario
    $stmt = $conn->prepare("UPDATE usuario SET usr_name = ?, usr_email = ?, imagen = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nombre, $email, $nueva_imagen, $usuario_id);

    if ($stmt->execute()) {
        $_SESSION['usuario_nombre'] = $nombre;
        echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar el perfil.']);
    }
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Endpoint no encontrado']);
?>
