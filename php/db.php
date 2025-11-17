<?php

function getDbConnection() {
    $host = "localhost";
    $user = "root";
    $password = "35128"; // Para producción, considera usar variables de entorno.
    $database = "dbuzz";

    try {
        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $password);

        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;

    } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage()); // Log the detailed error
            throw new PDOException('Error de conexión a la base de datos. Por favor, inténtelo de nuevo más tarde.', (int)$e->getCode()); // Generic message for user
        }
}

/**
 * Fetches company data from the 'empresa' table.
 * Assumes there's only one company record (id=1).
 * @return array|null Company data or null if not found.
 */
function getCompanyData() {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT rif, nombre, direccion, telefono, email, logo_svg FROM empresa WHERE id = 1 LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Updates company data in the 'empresa' table.
 * Inserts if no record exists (id=1), otherwise updates.
 * @param string $rif
 * @param string $nombre
 * @param string $direccion
 * @param string $telefono
 * @param string $email
 * @param string|null $logo_filename
 * @return bool True on success, false on failure.
 */
function updateCompanyData($rif, $nombre, $direccion, $telefono, $email, $logo_filename = null) {
    $pdo = getDbConnection();

    $sql = "INSERT INTO empresa (id, rif, nombre, direccion, telefono, email, logo_svg, actualizado_at)
            VALUES (1, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                rif = VALUES(rif),
                nombre = VALUES(nombre),
                direccion = VALUES(direccion),
                telefono = VALUES(telefono),
                email = VALUES(email),
                actualizado_at = NOW()";

    $params = [$rif, $nombre, $direccion, $telefono, $email, $logo_filename];

    if ($logo_filename !== null) {
        $sql .= ", logo_svg = VALUES(logo_svg)";
    }

    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

?>