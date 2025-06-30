<?php

namespace App\Controllers\PreCheckin;

use App\Models\LogApi;

class PreCheckinHealthController
{
    public function check()
    {
        $response = ['status' => 'ok', 'message' => 'API is healthy', 'version' => '1.0.0.0', 'PMS' => 'UNICAMP PMS'];
        $statusCode = 200;
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($response);
        // Registrar SIEMPRE el log, incluso si el endpoint no existe o hay error
        LogApi::create(
            '/api/v1/health',
            $_SERVER,
            $response,
            $statusCode
        );
    }
}
