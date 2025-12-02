<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Get PDO connection
try {
    $pdo = getDbConnection();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión a la base de datos: " . $e->getMessage()]);
    exit();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Acceso denegado"]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

function validatePassword($password) {
    if (strlen($password) < 8) {
        return false;
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        return false;
    }
    return true;
}

if (isset($_GET['action']) && $_GET['action'] == 'get_sessions') {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) AS session_count FROM logs_login WHERE DATE(fecha) = CURDATE()");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($result);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error de base de datos al obtener sesiones: " . $e->getMessage()]);
    }
    exit();
}

switch ($method) {
    case 'GET':
        try {
            $stmt = $pdo->prepare("SELECT u.id, u.nombre, u.email, u.rol, u.estado, MAX(ll.fecha) AS ultimo_acceso FROM usuarios u LEFT JOIN logs_login ll ON u.id = ll.usuario_id GROUP BY u.id");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($users);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error de base de datos al obtener usuarios: " . $e->getMessage()]);
        }
        break;

    case 'POST':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $nombre = $data['nombre'] ?? '';
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $rol = $data['rol'] ?? 'user';
            $estado = $data['estado'] ?? 'active';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(["error" => "Formato de correo electrónico no válido."]);
                exit();
            }

            if (!validatePassword($password)) {
                http_response_code(400);
                echo json_encode(["error" => "La contraseña no cumple con los requisitos de seguridad."]);
                exit();
            }

            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(["error" => "El correo electrónico ya está en uso."]);
                exit();
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, estado) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $email, $hashedPassword, $rol, $estado]);

            echo json_encode(["message" => "Usuario creado", "id" => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error de base de datos al crear usuario: " . $e->getMessage()]);
        }
        break;

    case 'PUT':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $nombre = $data['nombre'] ?? '';
            $email = $data['email'] ?? '';
            $rol = $data['rol'] ?? 'user';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(["error" => "Formato de correo electrónico no válido."]);
                exit();
            }

            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(["error" => "El correo electrónico ya está en uso."]);
                exit();
            }

            $sql = "UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?";
            $params = [$nombre, $email, $rol, $id];

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            echo json_encode(["message" => "Usuario actualizado"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error de base de datos al actualizar usuario: " . $e->getMessage()]);
        }
        break;

    case 'PATCH':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $estado = $data['estado'] ?? '';

            if (empty($estado) || !in_array($estado, ['activo', 'inactivo'])) {
                http_response_code(400);
                echo json_encode(["error" => "Estado inválido."]);
                exit();
            }

            $stmt = $pdo->prepare("UPDATE usuarios SET estado = ? WHERE id = ?");
            $stmt->execute([$estado, $id]);

            echo json_encode(["message" => "Estado de usuario actualizado."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error de base de datos al actualizar estado del usuario: " . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
}
?>
