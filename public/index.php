<?php

require __DIR__ . '/../vendor/autoload.php';

use Bramus\Router\Router;
use Dotenv\Dotenv;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Comprobar y crear el schema si es necesario
try {
    \App\Services\DatabaseSchemaService::ensureSchema();
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al comprobar/crear el schema de la base de datos', 'message' => $e->getMessage()]);
    exit();
}

// Instanciar el router
$router = new Router();

// Cabeceras CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Fichero de rutas
require __DIR__ . '/../routes/api.php';

// Iniciar el router
$router->run();
