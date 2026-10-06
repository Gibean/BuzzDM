<?php
/**
 * Actualización Automática de Tasas de Cambio
 *
 * Estrategia de fuentes (en orden de prioridad):
 *   1. Scraping directo del BCV (bcv.org.ve) — USD y EUR oficiales
 *   2. Fallback a DolarApi.com — USD oficial y paralelo
 *
 * El scraping del BCV usa los selectores #dolar strong y #euro strong
 * que contienen la tasa en formato "451,50720000" (coma decimal).
 */

header('Content-Type: application/json');

// Autenticacion para acceso por HTTP.
//
// El endpoint no debe quedar abierto: sin esto, cualquiera que conozca la URL
// podria disparar sincronizaciones ilimitadas contra el BCV y el proveedor
// externo (degradacion de servicio y spam de requests). Se admite:
//   - CLI, para el cron del hosting;
//   - HTTP con el token compartido BACKUP_SECRET_TOKEN, que es lo que envia
//     backup_check.php;
//   - HTTP con sesion autenticada, que es el boton "sincronizar" del dashboard.
//
// La comparacion del token falla cerrada si el token no esta definido.
if (php_sapi_name() !== 'cli') {
    require_once __DIR__ . '/backup_config.php';

    $providedToken = (string)($_GET['token'] ?? ($_SERVER['HTTP_X_CRON_TOKEN'] ?? ''));
    $tokenOk = BACKUP_SECRET_TOKEN !== ''
        && $providedToken !== ''
        && hash_equals(BACKUP_SECRET_TOKEN, $providedToken);

    // Sesion autenticada: solo se comprueba el identificador, no se carga
    // sesion.php porque su logica de permisos cerraria el endpoint.
    if (!$tokenOk) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $sessionOk = !empty($_SESSION['usuario_id']);
    } else {
        $sessionOk = false;
    }

    if (!$tokenOk && !$sessionOk) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error'   => 'Unauthorized',
        ]);
        exit;
    }
}

// Definir si es carga silenciosa (llamada desde backup_check / fallback)
$isSilent = isset($is_fallback_call) && $is_fallback_call;
$is_api_call = (php_sapi_name() !== 'cli' && !$isSilent);

try {
    require_once __DIR__ . '/db.php';
    $pdo = getDbConnection();
    $pdo->beginTransaction();

    // 1. Verificar si ya tenemos la tasa bcv de hoy para evitar spam
    $stmt = $pdo->query("SELECT creado_at FROM tasas_cambio WHERE fecha = CURDATE() AND fuente = 'bcv' LIMIT 1");
    $existingRate = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingRate) {
        $lastUpdate = strtotime($existingRate['creado_at']);
        if ((time() - $lastUpdate) < 14400 && !$is_api_call) {
            $pdo->rollBack();
            exit("Tasa bcv reciente. Saltando sync.\n");
        }
    }

    $syncResults = [];
    $sourceUsed = 'none';

    // =============================================
    // FUENTE PRIMARIA: Scraping directo del BCV
    // =============================================
    $bcvRates = fetchBcvDirectly();

    if ($bcvRates !== null) {
        $sourceUsed = 'bcv_directo';

        // Guardar tasa USD (BCV oficial)
        if (isset($bcvRates['usd']) && $bcvRates['usd'] > 0) {
            upsertTasa($pdo, $bcvRates['usd'], 'bcv');
            $syncResults['bcv'] = $bcvRates['usd'];
        }

        // Guardar tasa EUR (BCV oficial directa — sin cálculo cruzado)
        if (isset($bcvRates['eur']) && $bcvRates['eur'] > 0) {
            upsertTasa($pdo, $bcvRates['eur'], 'bcv_euro');
            $syncResults['bcv_euro'] = $bcvRates['eur'];
        }
    }

    // =============================================
    // FUENTE COMPLEMENTARIA: DolarApi.com
    // Se usa como fallback para BCV/EUR si el scraping falló,
    // y SIEMPRE para obtener tasa paralelo + USDT.
    // =============================================
    $apiUrl = "https://ve.dolarapi.com/v1/dolares";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $dolarApiResponse = curl_exec($ch);
    $dolarApiHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($dolarApiHttpCode === 200) {
        $rates = json_decode($dolarApiResponse, true);
        if (is_array($rates)) {
            foreach ($rates as $rate) {
                $fuenteName = ($rate['fuente'] === 'oficial') ? 'bcv' : 'paralelo';
                $tasaValue = floatval($rate['promedio']);

                if ($tasaValue > 0) {
                    if ($fuenteName === 'bcv' && isset($syncResults['bcv'])) {
                        // Ya tenemos BCV del scraping directo, no sobreescribir
                        continue;
                    }
                    upsertTasa($pdo, $tasaValue, $fuenteName);
                    $syncResults[$fuenteName] = $tasaValue;
                    if ($fuenteName === 'bcv' && $sourceUsed === 'none') {
                        $sourceUsed = 'dolarapi_fallback';
                    }
                }
            }
        }
    }

    // Calcular EUR por cross-rate solo si no lo tenemos del BCV directo
    if (isset($syncResults['bcv']) && !isset($syncResults['bcv_euro'])) {
        $eurUsdCross = fetchEurUsdCrossRate();
        $tasaEuro = $syncResults['bcv'] * $eurUsdCross;
        upsertTasa($pdo, $tasaEuro, 'bcv_euro');
        $syncResults['bcv_euro'] = $tasaEuro;
    }

    // =============================================
    // USDT estimado (basado en paralelo)
    // =============================================
    if (isset($syncResults['paralelo'])) {
        $tasaUsdt = $syncResults['paralelo'] * 0.995;
        upsertTasa($pdo, $tasaUsdt, 'binance_usdt');
        $syncResults['binance_usdt'] = $tasaUsdt;
    }

    $pdo->commit();

    if ($is_api_call) {
        echo json_encode([
            'success' => true,
            'message' => 'Sincronización completada.',
            'source' => $sourceUsed,
            'rates' => $syncResults
        ]);
    } else {
        echo "Sincronización completada ($sourceUsed). Tasas: " . implode(", ", array_keys($syncResults));
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


// =============================================
// Funciones auxiliares
// =============================================

/**
 * Scraping directo de bcv.org.ve
 * Obtiene USD y EUR desde los selectores #dolar strong / #euro strong
 *
 * @return array|null ['usd' => float, 'eur' => float] o null si falla
 */
function fetchBcvDirectly(): ?array {
    try {
        // Usar Symfony DomCrawler para parsear el HTML
        $crawler = new \Symfony\Component\DomCrawler\Crawler();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.bcv.org.ve');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($html)) {
            error_log("BCV scraping: HTTP $httpCode o respuesta vacía.");
            return null;
        }

        $crawler->addHtmlContent($html);

        $rates = [];

        // USD: #dolar strong — valor como "451,50720000"
        $dolarNode = $crawler->filter('#dolar strong');
        if ($dolarNode->count() > 0) {
            $rawValue = trim($dolarNode->text());
            $rates['usd'] = parseBcvNumber($rawValue);
        }

        // EUR: #euro strong — valor como "520,64198246"
        $euroNode = $crawler->filter('#euro strong');
        if ($euroNode->count() > 0) {
            $rawValue = trim($euroNode->text());
            $rates['eur'] = parseBcvNumber($rawValue);
        }

        // Validar que obtuvimos al menos USD
        if (!isset($rates['usd']) || $rates['usd'] <= 0) {
            error_log("BCV scraping: No se encontró tasa USD válida.");
            return null;
        }

        error_log("BCV scraping exitoso: USD={$rates['usd']}, EUR=" . ($rates['eur'] ?? 'N/A'));
        return $rates;

    } catch (Exception $e) {
        error_log("BCV scraping error: " . $e->getMessage());
        return null;
    }
}

/**
 * Parsea un número del formato BCV (coma decimal, punto miles)
 * Ejemplos: "451,50720000" → 451.5072, "1.234,56" → 1234.56
 *
 * @param string $value
 * @return float
 */
function parseBcvNumber(string $value): float {
    // Remover espacios y caracteres no numéricos excepto coma y punto
    $value = preg_replace('/[^\d,.]/', '', $value);

    // Si hay coma pero no punto → coma es decimal (ej: "451,507")
    // Si hay punto Y coma → punto es miles, coma es decimal (ej: "1.234,56")
    if (strpos($value, ',') !== false) {
        // Remover puntos de miles y cambiar coma decimal por punto
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
    }

    return floatval($value);
}

/**
 * Obtiene el cross-rate EUR/USD para cálculo de EUR (fallback)
 *
 * @return float Cross-rate (cuántos USD vale 1 EUR)
 */
function fetchEurUsdCrossRate(): float {
    $eurUsdCross = 1.09; // Default fallback

    $crossApi = "https://api.exchangerate-api.com/v4/latest/USD";
    $ch = curl_init($crossApi);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $crossResponse = curl_exec($ch);
    curl_close($ch);

    if ($crossResponse) {
        $crossData = json_decode($crossResponse, true);
        if (isset($crossData['rates']['EUR'])) {
            $eurUsdCross = 1 / floatval($crossData['rates']['EUR']);
        }
    }

    return $eurUsdCross;
}

/**
 * Inserta o actualiza una tasa de cambio para hoy
 *
 * @param PDO $pdo
 * @param float $tasa
 * @param string $fuente
 */
function upsertTasa(PDO $pdo, float $tasa, string $fuente): void {
    $sql = "INSERT INTO tasas_cambio (tasa, fuente, fecha, usuario_id)
            VALUES (:tasa, :fuente, CURDATE(), NULL)
            ON DUPLICATE KEY UPDATE
                tasa = :tasa_update,
                creado_at = CURRENT_TIMESTAMP";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':tasa' => $tasa, ':fuente' => $fuente, ':tasa_update' => $tasa]);
}
