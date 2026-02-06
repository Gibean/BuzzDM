<?php
require_once __DIR__ . '/../Repository/SettingsRepository.php';

class SettingsService {
    private $repository;

    public function __construct() {
        $this->repository = new SettingsRepository();
    }

    public function getCompanyInfo() {
        return $this->repository->findCompanyInfo();
    }

    public function updateCompanyInfo($data) {
        $rifType = $data['rifType'] ?? '';
        $rifNumber = $data['rifNumber'] ?? '';
        $nombre = $data['companyName'] ?? '';
        $direccion = $data['companyAddress'] ?? '';
        $phoneCode = $data['phoneCode'] ?? '';
        $phoneNumber = $data['phoneNumber'] ?? '';
        $email = $data['companyEmail'] ?? '';

        $fullRif = $rifType . $rifNumber;
        $fullPhone = trim($phoneCode) . ' ' . trim($phoneNumber);

        if (empty($fullRif) || empty($nombre) || empty($email)) {
            throw new Exception('RIF, Nombre y Email son campos obligatorios.');
        }

        $dbData = [
            'rif' => $fullRif,
            'nombre' => $nombre,
            'direccion' => $direccion,
            'telefono' => $fullPhone,
            'email' => $email
        ];

        if ($this->repository->companyExists()) {
            $this->repository->updateCompanyInfo($dbData);
        } else {
            $this->repository->createCompanyInfo($dbData);
        }
    }

    public function uploadLogo($file) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir el archivo.');
        }

        $fileMimeType = mime_content_type($file['tmp_name']);
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate type - accept PNG or JPG only
        $allowedMimes = ['image/png', 'image/jpeg', 'image/jpg'];
        $allowedExts = ['png', 'jpg', 'jpeg'];

        if (!in_array($fileMimeType, $allowedMimes) || !in_array($fileExtension, $allowedExts)) {
            throw new Exception('Formato de archivo no permitido. Solo se aceptan archivos PNG o JPG.');
        }

        // Validate size (6MB)
        if ($file['size'] > 6 * 1024 * 1024) {
            throw new Exception('El archivo es demasiado grande. El tamaño máximo permitido es 6MB.');
        }

        // Check GD support for WebP
        if (!function_exists('imagecreatefrompng') || !function_exists('imagewebp')) {
            throw new Exception('La extensión GD con soporte WebP no está disponible en el servidor.');
        }

        $companyInfo = $this->repository->findCompanyInfo();
        if (!$companyInfo || empty($companyInfo['rif'])) {
            throw new Exception('No se pudo obtener el RIF de la empresa para nombrar el logo. Guarde la información de la empresa primero.');
        }

        $uploadDir = __DIR__ . '/../../../assets/img/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Convert to WebP using GD
        $image = null;
        if ($fileMimeType === 'image/png') {
            $image = imagecreatefrompng($file['tmp_name']);
            // Preserve transparency
            imagesavealpha($image, true);
            imagealphablending($image, true);
        } else {
            $image = imagecreatefromjpeg($file['tmp_name']);
        }

        if (!$image) {
            throw new Exception('Error al procesar la imagen.');
        }

        $fileName = $companyInfo['rif'] . '.webp';
        $targetFilePath = $uploadDir . $fileName;

        // Save as WebP with 90% quality
        $success = imagewebp($image, $targetFilePath, 90);
        imagedestroy($image);

        if ($success) {
            $logoPathForDb = 'assets/img/uploads/' . $fileName;
            $this->repository->updateLogo($logoPathForDb);
            return true;
        } else {
            throw new Exception('Error al convertir la imagen a WebP.');
        }
    }
}
