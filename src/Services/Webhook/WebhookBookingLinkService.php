<?php

namespace App\Services\Webhook;

class WebhookBookingLinkService
{
    private $baseUrl;
    private $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(getenv('ROOMMATIK_WEBHOOK_BASE_URL') ?: 'https://api.roommatik.com/webhook', '/');
        $this->token = getenv('ROOMMATIK_OUTCOMING_API_KEY') ?: '';
    }

    /**
     * Realiza una petición PATCH a la API externa Roommatik Webhook con reservationCode en la URL
     * @param string $reservationCode El id_reserva a incluir en la URL
     * @param array $reservationData El array de reserva generado por XmlToReservationJson::map($json)
     * @return array ['status' => int, 'body' => string]
     */
    public function requestLink($reservationCode)
    {
        $url = $this->baseUrl . '/bookingLink/' . urlencode($reservationCode);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
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
}
