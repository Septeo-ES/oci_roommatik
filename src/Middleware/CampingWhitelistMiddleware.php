<?php

namespace App\Middleware;

use App\Models\Database;

class CampingWhitelistMiddleware
{
    public function __invoke()
    {
        // Obtener el hotel_id del header, query param o del body
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $campingId = $headers['hotel_id'] ?? null;
        if (!$campingId) {
            $campingId = $_GET['hotel_id'] ?? null;
        }
        if (!$campingId && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            $input = json_decode(file_get_contents('php://input'), true);
            $campingId = $input['hotel_id'] ?? null;
        }
        if (!$campingId) {
            http_response_code(400);
            echo json_encode(['error' => 'hotel_id es obligatorio para acceder a este servicio.']);
            exit();
        }
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT 1 FROM lista_Blanca_Camping_Roommatik WHERE camping_id = ?');
        $stmt->execute([$campingId]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['error' => 'El camping no tiene activado el servicio Roommatik.']);
            exit();
        }
    }
}
