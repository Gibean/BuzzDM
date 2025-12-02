<?php
require_once 'sesion.php';
require_once 'db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Ensure only admin can access this
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    $response['message'] = 'Acceso denegado. Solo administradores pueden modificar la configuración.';
    echo json_encode($response);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_company_info':
        try {
            $stmt = $pdo->query("SELECT rif, nombre, direccion, telefono, email, logo_svg FROM empresa LIMIT 1");
            $companyInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($companyInfo) {
                $response['success'] = true;
                $response['data'] = $companyInfo;
            } else {
                $response['message'] = 'No se encontró información de la empresa.';
            }
        } catch (PDOException $e) {
            error_log("Error al obtener información de la empresa: " . $e->getMessage());
            $response['message'] = 'Error de base de datos al obtener información.';
        }
        break;

    case 'update_company_info':
        // Ensure it's a POST request for updates
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response['message'] = 'Método no permitido para esta acción.';
            break;
        }

        $rifType = $_POST['rifType'] ?? '';
        $rifNumber = $_POST['rifNumber'] ?? '';
        $nombre = $_POST['companyName'] ?? '';
        $direccion = $_POST['companyAddress'] ?? '';
        $phoneCode = $_POST['phoneCode'] ?? '';
        $phoneNumber = $_POST['phoneNumber'] ?? '';
        $email = $_POST['companyEmail'] ?? '';

        $fullRif = $rifType . $rifNumber;
        $fullPhone = $phoneCode . $phoneNumber;

        // Basic validation
        if (empty($fullRif) || empty($nombre) || empty($email)) {
            $response['message'] = 'RIF, Nombre y Email son campos obligatorios.';
            echo json_encode($response);
            exit();
        }

        try {
            // Check if company info exists (assuming only one row)
            $stmtCheck = $pdo->query("SELECT COUNT(*) FROM empresa");
            $exists = $stmtCheck->fetchColumn();

            if ($exists > 0) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE empresa SET rif = ?, nombre = ?, direccion = ?, telefono = ?, email = ?, actualizado_at = NOW() WHERE id = 1");
                $stmt->execute([$fullRif, $nombre, $direccion, $fullPhone, $email]);
            } else {
                // Insert new (should only happen once)
                $stmt = $pdo->prepare("INSERT INTO empresa (rif, nombre, direccion, telefono, email) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$fullRif, $nombre, $direccion, $fullPhone, $email]);
            }

            $response['success'] = true;
            $response['message'] = 'Información de la empresa actualizada exitosamente.';
        } catch (PDOException $e) {
            error_log("Error al actualizar información de la empresa: " . $e->getMessage());
            $response['message'] = 'Error de base de datos al actualizar información.';
        }
        break;

    case 'upload_logo':
        // Ensure it's a POST request for uploads
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response['message'] = 'Método no permitido para esta acción.';
            break;
        }

        if (!isset($_FILES['logoFile']) || $_FILES['logoFile']['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = 'Error al subir el archivo.';
            break;
        }

        $file = $_FILES['logoFile'];
        $fileMimeType = mime_content_type($file['tmp_name']);
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

        // Validate file type (SVG only)
        if ($fileMimeType !== 'image/svg+xml' || strtolower($fileExtension) !== 'svg') {
            $response['message'] = 'Formato de archivo no permitido. Solo se aceptan archivos SVG.';
            break;
        }

        // Validate file size (max 6MB)
        $maxFileSize = 6 * 1024 * 1024; // 6 MB in bytes
        if ($file['size'] > $maxFileSize) {
            $response['message'] = 'El archivo es demasiado grande. El tamaño máximo permitido es 6MB.';
            break;
        }

        // Get RIF to name the file
        $stmtRif = $pdo->query("SELECT rif FROM empresa LIMIT 1");
        $companyRif = $stmtRif->fetchColumn();

        if (!$companyRif) {
            $response['message'] = 'No se pudo obtener el RIF de la empresa para nombrar el logo. Por favor, guarde la información de la empresa primero.';
            break;
        }

        $uploadDir = '../assets/img/uploads/';
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = $companyRif . '.svg';
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            try {
                // Update logo_svg column with the path
                $logoPathForDb = 'assets/img/uploads/' . $fileName; // Path relative to htdocs
                $stmt = $pdo->prepare("UPDATE empresa SET logo_svg = ?, actualizado_at = NOW() WHERE id = 1");
                $stmt->execute([$logoPathForDb]);

                $response['success'] = true;
                $response['message'] = 'Logo subido y actualizado exitosamente.';
            } catch (PDOException $e) {
                error_log("Error de base de datos al actualizar la ruta del logo: " . $e->getMessage());
                $response['message'] = 'Error de base de datos al guardar la ruta del logo.';
                // Clean up uploaded file if DB update fails
                unlink($targetFilePath);
            }
        } else {
            $response['message'] = 'Error al mover el archivo subido.';
        }
        break;

    default:
        $response['message'] = 'Acción no válida.';
        break;
}

echo json_encode($response);
?>
