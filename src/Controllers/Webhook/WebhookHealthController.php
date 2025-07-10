<?php

namespace App\Controllers\Webhook;

use App\Services\Webhook\WebhookApiClient;
use App\Models\LogApi;

class WebhookHealthController
{
    public function check()
    {
        $client = new WebhookApiClient();
        $result = $client->get('health');
        http_response_code($result['status']);
        header('Content-Type: text/plain');
        echo $result['body'];
        LogApi::create('/api/v1/webhook/health', $_SERVER, $result['body'], $result['status']);
    }
}
