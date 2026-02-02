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
        $fullPhone = $phoneCode . $phoneNumber;

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
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

        // Validate type
        if ($fileMimeType !== 'image/svg+xml' || strtolower($fileExtension) !== 'svg') {
             throw new Exception('Formato de archivo no permitido. Solo se aceptan archivos SVG.');
        }

        // Validate size (6MB)
        if ($file['size'] > 6 * 1024 * 1024) {
            throw new Exception('El archivo es demasiado grande. El tamaño máximo permitido es 6MB.');
        }

        $companyInfo = $this->repository->findCompanyInfo();
        if (!$companyInfo || empty($companyInfo['rif'])) {
            throw new Exception('No se pudo obtener el RIF de la empresa para nombrar el logo. Guarde la información de la empresa primero.');
        }

        $uploadDir = __DIR__ . '/../../../assets/img/uploads/'; // Adjust path relative to Service/
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = $companyInfo['rif'] . '.svg';
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            $logoPathForDb = 'assets/img/uploads/' . $fileName;
            $this->repository->updateLogo($logoPathForDb);
            return true;
        } else {
            throw new Exception('Error al mover el archivo subido.');
        }
    }
}
