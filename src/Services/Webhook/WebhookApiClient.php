<?php

namespace App\Services\Webhook;

class WebhookApiClient
{
    private $baseUrl;

    public function __construct()
    {
        $baseUrl = getenv('ROOMMATIK_WEBHOOK_BASE_URL') ?: 'https://api.roommatik.com/webhook';
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Realiza una petición GET al endpoint indicado
     */
    public function get($endpoint)
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $headerSize);
        curl_close($ch);
        return [
            'status' => $httpCode,
            'body' => trim($body)
        ];
    }
}
