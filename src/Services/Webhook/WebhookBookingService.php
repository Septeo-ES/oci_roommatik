<?php

namespace App\Services\Webhook;

class WebhookBookingService
{
    private $baseUrl;
    private $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(getenv('ROOMMATIK_WEBHOOK_BASE_URL') ?: 'https://api.roommatik.com/webhook', '/');
        $this->token = getenv('ROOMMATIK_OUTCOMING_API_KEY') ?: '';
    }

    /**
     * Envía la información de la reserva a la API externa de Roommatik Webhook
     * @param array $reservationData El array de reserva generado por XmlToReservationJson::map($json)
     * @return array ['status' => int, 'body' => string]
     */
    public function sendBooking($reservationData)
    {
        $url = $this->baseUrl . '/booking';
        $payload = json_encode($reservationData);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . $this->token
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [
            'status' => $httpCode,
            'body' => $response
        ];
    }

    /**
     * Realiza una petición PATCH a la API externa Roommatik Webhook con reservationCode en la URL
     * @param string $reservationCode El id_reserva a incluir en la URL
     * @param array $reservationData El array de reserva generado por XmlToReservationJson::map($json)
     * @return array ['status' => int, 'body' => string]
     */
    public function patchBooking($reservationData, $reservationCode)
    {
        $url = $this->baseUrl . '/booking/' . urlencode($reservationCode);
        $payload = json_encode($reservationData);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . $this->token
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [
            'status' => $httpCode,
            'body' => $response
        ];
    }

    /**
     * Realiza una petición DELETE a la API externa Roommatik Webhook con id_reserva y reserva en la URL
     * @param string $idReserva
     * @param string $reservaCode
     * @return array ['status' => int, 'body' => string]
     */
    public function deleteBooking($idReserva, $reservaCode)
    {
        $url = $this->baseUrl . '/booking/' . urlencode($idReserva) . '/' . urlencode($reservaCode);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $this->token
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [
            'status' => $httpCode,
            'body' => $response
        ];
    }
}
