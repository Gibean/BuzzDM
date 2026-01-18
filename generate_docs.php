<?php
require 'vendor/autoload.php';

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
