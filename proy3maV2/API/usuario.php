<?php
/* Clase usuario para gestionar con API RESTful
 * Permite operaciones CRUD (Crear, Leer, Actualizar, Eliminar)
 * Requiere conexión a una base de datos MySQL
 */

// Configuracion del reporte de errores
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

class Usuario
{
	private $conn;

	// Constructor que recibe la conexión a la base de datos
	public function __construct($conn)
	{
		$this->conn = $conn;
	}

	// Métodos para manejar usuarios
	// Obtener todos los usuarios
	public function getAllUsuarios()
	{
		$query = "SELECT * FROM usuario";
		$result = mysqli_query($this->conn, $query);
		$usuarios = [];
		while($row = mysqli_fetch_assoc($result)) {
			$usuarios[] = $row;
		}
		return $usuarios;
	}
	// Obtener un usuario por ID
	public function getUsuarioById($id)
	{
		$query = "SELECT * FROM usuario WHERE id = $id ";
		$result = mysqli_query($this->conn, $query);
		$usuario = mysqli_fetch_assoc($result);
		return $usuario;
	}
	// Agregar un nuevo usuario
	public function addUsuario($data)
	{
		if(!isset($data['usr_name']) || !isset($data['imagen']) || !isset($data['usr_email']) || !isset($data['usr_pass'])) {
			http_response_code(400);
			echo json_encode(["error" => "Datos incompletos"]);
		}else{
			$usr_name = $data['usr_name'];
			$usr_email = $data['usr_email'];
			$usr_pass = password_hash($data['usr_pass'], PASSWORD_DEFAULT);

		$checkQuery = "SELECT * FROM usuario WHERE usr_email = '$usr_email'";
		$checkResult = mysqli_query($this->conn, $checkQuery);

		if (mysqli_num_rows($checkResult) > 0) {
			http_response_code(409); 
			echo json_encode(["error" => "El email ya está registrado, intenta ingresarlo nuevamente"]);
			exit; 
		}
			// Procesar imagen base64
			$img_data = $data['imagen'];
			if (preg_match('/^data:image\/(\w+);base64,/', $img_data, $type)) {
				$img_data = substr($img_data, strpos($img_data, ',') + 1);
				$img_data = base64_decode($img_data);
				$ext = strtolower($type[1]);
				$img_name = uniqid() . "." . $ext;
				$img_path = __DIR__ . "/uploads/" . $img_name;
				if (!is_dir(__DIR__ . "/uploads/")) {
					mkdir(__DIR__ . "/uploads/", 0777, true);
				}
				if (file_put_contents($img_path, $img_data) === false) {
					http_response_code(500);
					echo json_encode(["error" => "No se pudo guardar la imagen"]);
					exit;
				}
			} else {
				http_response_code(400);
				echo json_encode(["error" => "Formato de imagen inválido"]);
				exit;
			}
		$query = "INSERT INTO usuario (usr_name, usr_email, usr_pass, imagen) 
          VALUES ('$usr_name', '$usr_email', '$usr_pass', '$img_name')";
$result = mysqli_query($this->conn, $query);

if ($result) {
    http_response_code(201); 
    echo json_encode(["mensaje" => "Usuario registrado correctamente"]);
    exit;
} else {
    http_response_code(500); // Error del servidor
    echo json_encode(["error" => "Error al registrar el usuario", "detalle" => mysqli_error($this->conn)]);
    exit;
}

	}
}
	// Iniciar sesión de usuario
	public function loginUsuario($usr_email, $usr_pass)
{
    session_set_cookie_params([
        'path' => '/',
        'domain' => 'localhost',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $query = "SELECT * FROM usuario WHERE usr_email = '$usr_email'";
    $result = mysqli_query($this->conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $usuario = mysqli_fetch_assoc($result);

        if ($usuario['validado'] == 1) {
            if (password_verify($usr_pass, $usuario['usr_pass'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['usr_name'];
                $_SESSION['usuario_email'] = $usuario['usr_email'];
                $_SESSION['validado'] = $usuario['validado'];

                return $usuario; //  Usuario válido
            } else {
                return false; //  Contraseña incorrecta
            }
        } elseif ($usuario['validado'] == 0) {
            return ["status" => "pendiente", "msg" => "Tu cuenta está pendiente de aprobación"];
        } elseif ($usuario['validado'] == 2) {
            return ["status" => "rechazado", "msg" => "Tu registro fue rechazado"];
        }
    } else {
        return false; //  No existe usuario
    }
}

	

	// Actualizar un usuario por ID
	public function updateUsuario($id, $data)
	{
		$usr_name = $data['usr_name'];
		$usr_email = $data['usr_email'];
		$usr_pass = $data['usr_pass'];
		$query = "UPDATE usuario SET usr_name = '$usr_name', usr_email = '$usr_email', usr_pass = '$usr_pass' WHERE id = ".$id;
		$result = mysqli_query($this->conn, $query);
		if($result){
			return true;
		} else {
			return false;
		}
	}
	// Eliminar un usuario por ID
	public function deleteUsuario($id)
	{
		$query = "DELETE FROM usuario WHERE id = ".$id;
		$result = mysqli_query($this->conn, $query);
		if($result){
			return true;
		} else {
			return false;
		}
	}
}