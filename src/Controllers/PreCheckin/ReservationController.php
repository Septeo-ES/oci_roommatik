<?php

namespace App\Controllers\PreCheckin;

use App\Models\Database;
use App\Models\LogApi;
use App\Utils\XmlToReservationJson;

class ReservationController
{
    public function get()
    {
        $headers = getallheaders();
        $bookingCode = $_GET['bookingCode'] ?? null;
        // Prioridad: header 'hotel_id' (case-insensitive), luego query param
        $campingId = null;
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'hotel_id') {
                $campingId = $value;
                break;
            }
        }
        if (!$campingId) {
            $campingId = $_GET['hotel_id'] ?? null;
        }
        $statusCode = 200;
        $response = null;

        if (!$bookingCode) {
            $statusCode = 400;
            $response = [
                'returnCode' => 1,
                'description' => 'Falta el parámetro bookingCode en la cabecera.'
            ];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create('/api/v1/reservation', $_SERVER, $response, $statusCode);
            return;
        }

        $db = Database::getInstance()->getConnection();
        try {
            // Buscar localizador a partir de id_reserva = bookingCode
            $stmt = $db->prepare('SELECT localizador FROM reserva_localizador WHERE id_reserva = ?');
            $stmt->execute([$bookingCode]);
            $row = $stmt->fetch();
            if (!$row) {
                $statusCode = 404;
                $response = [
                    'returnCode' => 2,
                    'description' => 'No se encontró el localizador para el bookingCode proporcionado.'
                ];
                http_response_code($statusCode);
                echo json_encode($response);
                LogApi::create('/api/v1/reservation', $_SERVER, $response, $statusCode);
                return;
            }
            $localizador = $row['localizador'];
            // Buscar xml y xml_final en reserva
            $stmt = $db->prepare('SELECT xml, xml_final FROM reserva WHERE localizador = ? AND idCamping = ?');
            $stmt->execute([$localizador, $campingId]);
            $reserva = $stmt->fetch();
            if (!$reserva) {
                $statusCode = 404;
                $response = [
                    'returnCode' => 3,
                    'description' => 'No se encontró la reserva para el localizador proporcionado.'
                ];
                http_response_code($statusCode);
                echo json_encode($response);
                LogApi::create('/api/v1/reservation', $_SERVER, $response, $statusCode);
                return;
            }
            $xml = trim($reserva['xml_final']) !== '' ? $reserva['xml_final'] : $reserva['xml'];
            // Parsear el XML a array (simplexml + json_encode)
            $xmlObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $json = json_decode(json_encode($xmlObj), true);
            // Mapear el XML a la estructura de respuesta requerida
            $result = XmlToReservationJson::map($json);
            $response = [
                'returnCode' => 0,
                'description' => 'Success',
                'result' => $result,
                'pmsMessage' => null,
                'userMessage' => null
            ];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create('/api/v1/reservation', $_SERVER, $response, $statusCode);
        } catch (\PDOException $e) {
            $statusCode = 500;
            $response = [
                'returnCode' => 99,
                'description' => 'Error de base de datos: ' . $e->getMessage()
            ];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create('/api/v1/reservation', $_SERVER, $response, $statusCode);
        }
    }
}
