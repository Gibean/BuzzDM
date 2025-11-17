<?php
require_once 'sesion.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rif = $_POST['rif'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';

    $logo_filename = null;

    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['logo']['tmp_name'];
        $fileName = $_FILES['logo']['name'];
        $fileSize = $_FILES['logo']['size'];
        $fileType = $_FILES['logo']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('png', 'svg');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Sanitize RIF for filename
            $sanitized_rif = preg_replace("/[^a-zA-Z0-9-]/", "", $rif); // Keep only alphanumeric and hyphens
            $newFileName = $sanitized_rif . '.' . $fileExtension;
            $uploadFileDir = '../uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $logo_filename = $newFileName;
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error al subir el logo.'];
                header('Location: ../company_settings.php');
                exit();
            }
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Tipo de archivo no permitido para el logo. Solo PNG o SVG.'];
            header('Location: ../company_settings.php');
            exit();
        }
    }

    // Update company data in DB
    try {
        $success = updateCompanyData($rif, $nombre, $direccion, $telefono, $email, $logo_filename);

        if ($success) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Configuración de empresa guardada exitosamente.'];
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error al guardar la configuración de empresa.'];
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error de base de datos: ' . $e->getMessage()];
    }

    header('Location: ../company_settings.php');
    exit();
} else {
    header('Location: ../company_settings.php');
    exit();
}
?>