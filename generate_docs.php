<?php
// Escritura sin autenticacion: reachable como /generate_docs, /generate_docs.php y
// generaba openapi.yaml para cualquiera que visitara la URL.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("403 - Solo ejecutable desde CLI: php generate_docs.php\n");
}

require __DIR__ . '/vendor/autoload.php';

// api_docs_config.php se carga de forma explicita y no por el autoloader.
// Estaba en autoload.files, lo que hacia que cada peticion de la aplicacion
// arrastrara un archivo con atributos de OpenApi que solo interesan a esta
// generacion. Aqui solo se necesita cuando se regenera la documentacion.
require_once __DIR__ . '/php/api_docs_config.php';

use OpenApi\Generator;

// Scan the src directory where Controllers and Entities defined
// Also include key config files
// In newer versions scan might be via instance or function
$generator = new Generator();
$openapi = $generator->generate([
    __DIR__ . '/php/src/Controller',
    __DIR__ . '/php/src/Repository',
    __DIR__ . '/php/api_docs_config.php'
]);

// Set output file
$outputFile = __DIR__ . '/openapi.yaml';
file_put_contents($outputFile, $openapi->toYaml());

echo "Documentation generated at: $outputFile\n";
