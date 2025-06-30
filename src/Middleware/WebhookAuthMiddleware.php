<?php

namespace App\Middleware;

class WebhookAuthMiddleware
{
    private $token;

    public function __construct()
    {
        $configToken = getenv('ROOMMATIK_WEBHOOK_TOKEN') ?: 'supersecreto123';
        $this->token = $configToken;
    }

    public function __invoke()
    {
        $headers = getallheaders();
        $authHeader = '';
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $authHeader = $value;
                break;
            }
        }
        if ($authHeader !== $this->token) {
            http_response_code(401);
            echo json_encode([
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing authorization token.'
            ]);
            exit;
        }
    }
}
