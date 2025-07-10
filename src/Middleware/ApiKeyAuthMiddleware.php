<?php

namespace App\Middleware;

use App\Models\LogApi;

class ApiKeyAuthMiddleware
{
    private $apiKeyEnvName;

    public function __construct($apiKeyEnvName)
    {
        $this->apiKeyEnvName = $apiKeyEnvName;
    }

    public function __invoke()
    {
        $apiKey = $_ENV[$this->apiKeyEnvName] ?? null;
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        $statusCode = 200;
        $response = null;
        $endpoint = $_SERVER['REQUEST_URI'] ?? '';

        if (!$apiKey) {
            $statusCode = 500;
            $response = ['error' => 'API key not configured on the server.'];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create($endpoint, $_SERVER, $response, $statusCode);
            exit();
        }

        if (!$authHeader) {
            $statusCode = 401;
            $response = ['error' => 'Authorization header is missing.'];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create($endpoint, $_SERVER, $response, $statusCode);
            exit();
        }

        if ($authHeader !== $apiKey) {
            $statusCode = 403;
            $response = ['error' => 'Invalid API key.'];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create($endpoint, $_SERVER, $response, $statusCode);
            exit();
        }

        // Si pasa la autenticación, no registrar aquí, se registrará en el controlador
    }
}
