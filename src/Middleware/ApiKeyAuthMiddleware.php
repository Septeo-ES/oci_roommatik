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

    function getAuthorizationHeader(): ?string {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
        }

        return null;
    }


    public function __invoke()
    {
        $apiKey = $_ENV[$this->apiKeyEnvName] ?? null;

        $authHeader = $this->getAuthorizationHeader();
        if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
            $authHeader = substr($authHeader, 7); // Remove 'Bearer ' prefix
        }

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
