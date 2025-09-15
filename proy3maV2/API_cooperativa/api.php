<?php
/* API RESTful para gestionar comprobantes de pago
 * Requiere conexión a una base de datos MySQL
 */
ini_set('display_errors', 1); // Activa la visualización de errores en pantalla
error_reporting(E_ALL); // Muestra todos los tipos de errores de PHP

// Importa las dependencias necesarias
require_once 'config.php'; 
require_once 'comprobante.php'; // Incluye la clase Comprobante (posiblemente con métodos adicionales)

session_start(); 
// Crea la instancia de la clase Comprobante, pasándole la conexión a la BD
$comprobanteObj = new Comprobante($conn);

// Obtiene el método de la solicitud HTTP (POST, PUT, GET, DELETE, etc.)
$method = $_SERVER['REQUEST_METHOD'];

// Obtiene el endpoint de la solicitud (ejemplo: "/comprobantes" o "/comprobantes/1/validar")
$endpoint = $_SERVER['PATH_INFO'];

// Establece el tipo de contenido de la respuesta como JSON
header('Content-Type: application/json');

// Procesa la solicitud según el método HTTP recibido

// Se obtiene el método HTTP con el que se hizo la petición (GET, POST, PUT, DELETE, etc.)
$method = $_SERVER['REQUEST_METHOD']; 

// Se obtiene el endpoint solicitado (la URL después del dominio).
// Ejemplo: si la URL es "http://localhost/api/comprobantes", $endpoint será "/comprobantes"
$endpoint = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); 

switch ($method) {

    // --------------------------------------------------------------------
    // CASO 1: SUBIR UN COMPROBANTE (POST /comprobantes)
    // --------------------------------------------------------------------
        // Si el endpoint solicitado es exactamente "/comprobantes"
       case 'POST':
    if ($endpoint === '/comprobantes') {
        if (isset($_FILES['imagen']) && isset($_POST['tipo'])) {
            // ID desde la sesión
            if (!isset($_SESSION['usuario_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'No hay usuario logueado']);
                exit;
            }
            $usuario_id = intval($_SESSION['usuario_id']);
            $tipo = $_POST['tipo']; 
            $fecha = time(); 

            $targetDir = __DIR__ . "/../uploads/pagos/";  
            if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

            $fileName = uniqid() . "_" . basename($_FILES["imagen"]["name"]);
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $targetFile)) {
                $sql = "INSERT INTO pagos (usuario_id, fecha, tipo, imagen, validado) VALUES (?, ?, ?, ?, 0)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiss", $usuario_id, $fecha, $tipo, $fileName);

                if ($stmt->execute()) {
                    http_response_code(201);
                    echo json_encode(['success' => true, 'message' => 'Comprobante subido y pendiente de validación']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Error al guardar en la base de datos']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Error al subir archivo']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos: tipo o imagen']);
        }
    }
    break;


    // CASO 2: VALIDAR UN COMPROBANTE (PUT /comprobantes/{id}/validar)
    
    case 'PUT':
        // Revisamos si el endpoint cumple el patrón "/comprobantes/{id}/validar"
        if (preg_match('#^/comprobantes/(\d+)/validar$#', $endpoint, $matches)) {
            $id = intval($matches[1]); // Obtenemos el ID del comprobante de la URL
            $data = json_decode(file_get_contents("php://input"), true); // Leemos el JSON enviado

            // Validamos que se haya enviado un "estado" y que sea correcto (1=aprobado, 2=rechazado)
            if (isset($data['estado']) && in_array($data['estado'], [1, 2])) {
                $estado = $data['estado']; // Estado a asignar

                // Actualizamos el campo "validado" en la base de datos
                $sql = "UPDATE pagos SET validado = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $estado, $id);

                // Si la actualización fue exitosa
                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Comprobante actualizado'
                    ]);
                } else {
                    // Error al ejecutar el update
                    http_response_code(500);
                    echo json_encode(['error' => 'Error al actualizar comprobante']);
                }
            } else {
                // Si el estado no es válido
                http_response_code(400);
                echo json_encode(['error' => 'Debe enviar estado = 1 (aprobado) o 2 (rechazado)']);
            }
        }
        break;
}

?>
