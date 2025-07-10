<?php

namespace App\Controllers\Webhook;

use App\Services\Webhook\WebhookBookingService;
use App\Models\Database;
use App\Models\LogApi;
use App\Utils\XmlToReservationJson;

class WebhookBookingController
{
    private function handleBookingWebhook($body, $method)
    {
        $localizador = $body['localizador'] ?? null;
        $campingId = $body['campingId'] ?? null;
        $logResponse = null;
        $logStatus = null;
        if (!$localizador || !$campingId) {
            $logResponse = [
                'returnCode' => 1,
                'description' => 'Faltan parámetros obligatorios (localizador, campingId) en el body.'
            ];
            $logStatus = 400;
            http_response_code(400);
            echo json_encode($logResponse);
            LogApi::create('/api/v1/webhook/booking', $_SERVER, $logResponse, $logStatus);
            return;
        }
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT xml FROM reserva WHERE localizador = ? AND idCamping = ?');
        $stmt->execute([$localizador, $campingId]);
        $reserva = $stmt->fetch();
        if (!$reserva) {
            $logResponse = [
                'returnCode' => 2,
                'description' => 'No se encontró la reserva para el localizador y campingId proporcionados.'
            ];
            $logStatus = 404;
            http_response_code(404);
            echo json_encode($logResponse);
            LogApi::create('/api/v1/webhook/booking', $_SERVER, $logResponse, $logStatus);
            return;
        }
        $xml = $reserva['xml'];
        $xmlObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_decode(json_encode($xmlObj), true);
        $reservationData = XmlToReservationJson::map($json);
        $service = new WebhookBookingService();

        switch ($method) {
            case 'PATCH':
                // Obtener el reservationCode desde la tabla reserva_localizador
                $stmtReservation = $db->prepare('SELECT id_reserva FROM reserva_localizador WHERE localizador = ?');
                $stmtReservation->execute([$localizador]);
                $reservationRow = $stmtReservation->fetch();
                $reservationCode = $reservationRow ? $reservationRow['id_reserva'] : null;
                if (!$reservationCode) {
                    $logResponse = [
                        'returnCode' => 3,
                        'description' => 'No se encontró el reservationCode para el localizador proporcionado.'
                    ];
                    $logStatus = 404;
                    http_response_code(404);
                    echo json_encode($logResponse);
                    LogApi::create('/api/v1/webhook/booking', $_SERVER, $logResponse, $logStatus);
                    return;
                }
                $result = $service->patchBooking($reservationData, $reservationCode);
                break;
            default:
                $result = $service->sendBooking($reservationData);
        }
        $logStatus = $result['status'];
        $logResponse = $result['body'];
        http_response_code($result['status']);
        header('Content-Type: application/json');
        echo $result['body'];
        // Si la respuesta es 200 y tiene campo result, actualizar URL_roommatik
        if ($result['status'] == 200) {
            $responseJson = json_decode($result['body'], true);
            if (isset($responseJson['result'])) {
                $stmtUpdate = $db->prepare('UPDATE reserva_localizador SET URL_roommatik = ? WHERE localizador = ?');
                $stmtUpdate->execute([$responseJson['result'], $localizador]);
            }
        }
        LogApi::create('/api/v1/webhook/booking', $_SERVER, $logResponse, $logStatus);
    }

    public function post()
    {
        $body = json_decode(file_get_contents('php://input'), true);
        $this->handleBookingWebhook($body, 'POST');
    }

    public function put()
    {
        $body = json_decode(file_get_contents('php://input'), true);
        $this->handleBookingWebhook($body, 'PUT');
    }

    public function patch()
    {
        $body = json_decode(file_get_contents('php://input'), true);
        $this->handleBookingWebhook($body, 'PATCH');
    }
    
    public function delete()
    {
        $body = json_decode(file_get_contents('php://input'), true);
        $localizador = $body['localizador'] ?? null;
        $campingId = $body['campingId'] ?? null;
        $logResponse = null;
        $logStatus = null;
        if (!$localizador || !$campingId) {
            $logResponse = [
                'returnCode' => 1,
                'description' => 'Faltan parámetros obligatorios (localizador, campingId) en el body.'
            ];
            $logStatus = 400;
            http_response_code(400);
            echo json_encode($logResponse);
            LogApi::create('/api/v1/webhook/booking', $_SERVER, $logResponse, $logStatus);
            return;
        }
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT rl.localizador, r.idCamping, r.reserva, rl.id_reserva FROM reserva_localizador rl JOIN reserva r ON rl.localizador = r.localizador WHERE rl.localizador = ? LIMIT 1');
        $stmt->execute([$localizador]);
        $row = $stmt->fetch();
        if (!$row) {
            $logResponse = [
                'returnCode' => 2,
                'description' => 'No se encontró la reserva para el localizador proporcionado.'
            ];
            $logStatus = 404;
            http_response_code(404);
            echo json_encode($logResponse);
            LogApi::create('/api/v1/webhook/booking', $_SERVER, $logResponse, $logStatus);
            return;
        }
        $idReserva = $row['id_reserva'];
        $reservaCode = $row['reserva'];
        $service = new WebhookBookingService();
        $result = $service->deleteBooking($idReserva, $reservaCode);
        $logStatus = $result['status'];
        $logResponse = $result['body'];
        http_response_code($result['status']);
        header('Content-Type: application/json');
        echo $result['body'];
        LogApi::create('/api/v1/webhook/booking', $_SERVER, $logResponse, $logStatus);
    }
}
