<?php

namespace App\Controllers\Webhook;

use App\Services\Webhook\WebhookBookingLinkService;
use App\Models\Database;
use App\Models\LogApi;

class WebhookBookingLinkController
{
    private function handleBookingLinkWebhook($body)
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
            LogApi::create('/api/v1/webhook/bookingLink', $_SERVER, $logResponse, $logStatus);
            return;
        }
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT id_reserva FROM reserva_localizador WHERE localizador = ?');
        $stmt->execute([$localizador]);
        $reserva = $stmt->fetch();
        if (!$reserva) {
            $logResponse = [
                'returnCode' => 2,
                'description' => 'No se encontró la reserva para el localizador proporcionado.'
            ];
            $logStatus = 404;
            http_response_code(404);
            echo json_encode($logResponse);
            LogApi::create('/api/v1/webhook/bookingLink', $_SERVER, $logResponse, $logStatus);
            return;
        }
        $reservationCode = $reserva['id_reserva'];
        $service = new WebhookBookingLinkService();
        $result = $service->requestLink($reservationCode);
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
        LogApi::create('/api/v1/webhook/bookingLink', $_SERVER, $logResponse, $logStatus);
    }

    public function get()
    {
        // Para GET, los parámetros pueden venir por query string o body. Aquí soportamos ambos.
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            // Si no hay body, intentamos obtener de $_GET
            $body = [
                'localizador' => $_GET['localizador'] ?? null,
                'campingId' => $_GET['campingId'] ?? null
            ];
        }
        $this->handleBookingLinkWebhook($body);
    }

}
