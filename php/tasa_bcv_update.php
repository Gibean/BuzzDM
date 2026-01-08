<?php
/**
 * Automático de Tasa BCV
 *
 * Este script consulta la API de DolarApi.com para obtener el valor oficial
 * del BCV y lo guarda en la base de datos de BuzzDM.
 */

// Si se llama vía web, devolver JSON
header('Content-Type: application/json');

// Definir si es carga silenciosa
$isSilent = isset($is_fallback_call) && $is_fallback_call;
$is_api_call = (php_sapi_name() !== 'cli' && !$isSilent);

try {
    require_once __DIR__ . '/db.php';
    $pdo = getDbConnection();
    $pdo->beginTransaction();

    // 1. Verificar si ya tenemos la tasa bcv de hoy para evitar spam
    // Buscamos específicamente la bcv
    $stmt = $pdo->query("SELECT creado_at FROM tasas_cambio WHERE fecha = CURDATE() AND fuente = 'bcv' LIMIT 1");
    $existingRate = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingRate) {
        $lastUpdate = strtotime($existingRate['creado_at']);
        if ((time() - $lastUpdate) < 14400 && !$is_api_call) {
             // Si no es API, salimos si es reciente
             $pdo->rollBack();
             exit("Tasa bcv reciente. Saltando sync.\n");
        }
    }

    // Array of rates to fetch and save
    $syncResults = [];

    // 1. Fetch from DolarApi (Official & Paralelo)
    $apiUrl = "https://ve.dolarapi.com/v1/dolares";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $rates = json_decode($response, true);
        if (is_array($rates)) {
            foreach ($rates as $rate) {
                $fuenteName = ($rate['fuente'] === 'oficial') ? 'bcv' : 'paralelo';
                $tasaValue = floatval($rate['promedio']);

                if ($tasaValue > 0) {
                    $sql = "INSERT INTO tasas_cambio (tasa, fuente, fecha, usuario_id)
                            VALUES (:tasa, :fuente, CURDATE(), NULL)
                            ON DUPLICATE KEY UPDATE
                                tasa = :tasa_update,
                                creado_at = CURRENT_TIMESTAMP";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':tasa' => $tasaValue, ':fuente' => $fuenteName, ':tasa_update' => $tasaValue]);
                    $syncResults[$fuenteName] = $tasaValue;
                }
            }
        }
    }

    // 2. Fetch EUR rate (Estimation based on Cross Rate if not direct)
    // We try to get EUR/USD from a public API
    $eurUsdCross = 1.09; // Default fallback
    $crossApi = "https://api.exchangerate-api.com/v4/latest/USD";
    $chCross = curl_init($crossApi);
    curl_setopt($chCross, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chCross, CURLOPT_TIMEOUT, 5);
    curl_setopt($chCross, CURLOPT_SSL_VERIFYPEER, false);
    $crossResponse = curl_exec($chCross);
    if ($crossResponse) {
        $crossData = json_decode($crossResponse, true);
        if (isset($crossData['rates']['EUR'])) {
            $eurUsdCross = 1 / floatval($crossData['rates']['EUR']);
        }
    }
    curl_close($chCross);

    // If we have BCV rate, calculate BCV Euro
    if (isset($syncResults['bcv'])) {
        $tasaEuro = $syncResults['bcv'] * $eurUsdCross;
        $sqlEuro = "INSERT INTO tasas_cambio (tasa, fuente, fecha, usuario_id)
                    VALUES (:tasa, 'bcv_euro', CURDATE(), NULL)
                    ON DUPLICATE KEY UPDATE
                        tasa = :tasa_update,
                        creado_at = CURRENT_TIMESTAMP";
        $stmtEuro = $pdo->prepare($sqlEuro);
        $stmtEuro->execute([':tasa' => $tasaEuro, ':tasa_update' => $tasaEuro]);
        $syncResults['bcv_euro'] = $tasaEuro;
    }

    // USDT is usually Parallel rate or very close
    if (isset($syncResults['paralelo'])) {
        $tasaUsdt = $syncResults['paralelo'] * 0.995; // Small spread typical for USDT
        $sqlUsdt = "INSERT INTO tasas_cambio (tasa, fuente, fecha, usuario_id)
                    VALUES (:tasa, 'binance_usdt', CURDATE(), NULL)
                    ON DUPLICATE KEY UPDATE
                        tasa = :tasa_update,
                        creado_at = CURRENT_TIMESTAMP";
        $stmtUsdt = $pdo->prepare($sqlUsdt);
        $stmtUsdt->execute([':tasa' => $tasaUsdt, ':tasa_update' => $tasaUsdt]);
        $syncResults['binance_usdt'] = $tasaUsdt;
    }

    $pdo->commit();

    if ($is_api_call) {
        echo json_encode([
            'success' => true,
            'message' => 'Sincronización multidivisa completada.',
            'rates' => $syncResults
        ]);
    } else {
        echo "Sincronización completada con éxito. Tasas: " . implode(", ", array_keys($syncResults));
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("BUZZ BCV Multi-Sync Error: " . $e->getMessage());
    if ($is_api_call) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } else {
        echo "Error: " . $e->getMessage();
    }
}
