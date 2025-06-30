<?php

namespace App\Models;

class LogApi
{
    public static function create($endpoint, $requestData, $responseData = null, $resultCode = null)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('INSERT INTO logs_api (endpoint, request_data, response_data, result_code) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $endpoint,
            json_encode($requestData),
            $responseData ? json_encode($responseData) : null,
            $resultCode
        ]);
        return $db->lastInsertId();
    }
}
