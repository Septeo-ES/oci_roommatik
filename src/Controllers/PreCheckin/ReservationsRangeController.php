<?php

namespace App\Controllers\PreCheckin;

use App\Models\Database;
use App\Models\LogApi;
use App\Utils\XmlToReservationJson;

class ReservationsRangeController
{
    public function get()
    {
        $headers = getallheaders();
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
        $dateFrom = $_GET['dateFrom'] ?? null;
        $dateTo = $_GET['dateTo'] ?? null;
        $statusCode = 200;
        $response = null;

        if (!$campingId || !$dateFrom || !$dateTo) {
            $statusCode = 400;
            $response = [
                'returnCode' => 1,
                'description' => 'Faltan parámetros obligatorios (hotel_id, dateFrom, dateTo).'
            ];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create('/api/v1/reservations-range', $_SERVER, $response, $statusCode);
            return;
        }

        // Convertir dateFrom y dateTo a formato YYYYMMDD para comparar con <Del> y <AL>
        $dateFromYMD = substr($dateFrom, 0, 8);
        $dateToYMD = substr($dateTo, 0, 8);

        $db = Database::getInstance()->getConnection();
        try {
            // Buscar reservas por campingId y subida <= dateTo
            $stmt = $db->prepare('SELECT xml FROM reserva WHERE idCamping = ? AND subida <= ? AND estado < 3'); // estado < 3 para excluir reservas validadas.
            $stmt->execute([$campingId, $dateToYMD]);
            $reservas = $stmt->fetchAll();
            //error_log('DEBUG $stmt: ' . print_r($stmt, true));
            $result = [];
            foreach ($reservas as $reserva) {
                $xml = $reserva['xml'];
                $xmlObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
                $json = json_decode(json_encode($xmlObj), true);
                //error_log('DEBUG $xml: ' . print_r($xml, true));
                // Extraer fechas Del y AL del XML: ruta ROOT-Estancia-Del y ROOT-Estancia-AL
                $del = null;
                $al = null;
                if (isset($json['Estancia'])) {
                    $estancia = $json['Estancia'];
                    if (isset($estancia['Del'])) {
                        $del = $estancia['Del'];
                    }
                    if (isset($estancia['AL'])) {
                        $al = $estancia['AL'];
                    }
                }
                if ($del && $al) {
                    // Convertir fechas Del y AL de DD/MM/YYYY a YYYYMMDD
                    $delParts = explode('/', $del);
                    $alParts = explode('/', $al);
                    if (count($delParts) === 3 && count($alParts) === 3) {
                        $delYMD = $delParts[2] . $delParts[1] . $delParts[0];
                        $alYMD = $alParts[2] . $alParts[1] . $alParts[0];
                        // Comprobar si el rango de la reserva está dentro del rango solicitado
                        if ($delYMD <= $dateToYMD && $alYMD >= $dateFromYMD) {
                            $result[] = XmlToReservationJson::map($json);
                        }
                    }
                }
            }
            $response = [
                'returnCode' => 0,
                'description' => 'Success',
                'result' => [
                    'Results' => count($result),
                    'Reservations' => $result
                ],
                'pmsMessage' => null,
                'userMessage' => null
            ];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create('/api/v1/reservations-range', $_SERVER, $response, $statusCode);
        } catch (\PDOException $e) {
            $statusCode = 500;
            $response = [
                'returnCode' => 99,
                'description' => 'Error de base de datos: ' . $e->getMessage()
            ];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create('/api/v1/reservations-range', $_SERVER, $response, $statusCode);
        }
    }
}
