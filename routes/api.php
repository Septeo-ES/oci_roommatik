<?php

use App\Controllers\PreCheckin\PreCheckinHealthController;
use App\Controllers\PreCheckin\ReservationController;
use App\Controllers\PreCheckin\NewGuestController;
use App\Controllers\PreCheckin\ReservationsRangeController;
use App\Middleware\ApiKeyAuthMiddleware;
use App\Middleware\CampingWhitelistMiddleware;
use App\Middleware\WebhookAuthMiddleware;
use App\Controllers\Webhook\WebhookHealthController;
use App\Controllers\Webhook\WebhookBookingController;

// Rutas para la colección C_Roommatik_OCI_PreCheckin_v.1.0.1 (Endpoints que nosotros exponemos) --- EXPOSICIÓN PÚBLICA ---
$router->before('GET|POST|PUT|DELETE', '/api/v1/.*', function() {
    (new ApiKeyAuthMiddleware('ROOMMATIK_INCOMING_API_KEY'))();
    (new CampingWhitelistMiddleware())();
});

$router->get('/api/v1/health', function() {
    (new PreCheckinHealthController())->check();
});

$router->get('/api/v1/reservation', function() {
    (new ReservationController())->get();
});

$router->post('/api/v1/newguest', function() {
    (new NewGuestController())->post();
});

$router->get('/api/v1/reservationsbydate', function() {
    (new ReservationsRangeController())->get();
});

// Rutas para la colección C_Roommatik_OCI_Webhook_v.1.0.1 (Endpoints que Roommatik expone a nosotros) --- EXPOSICIÓN PRIVADA ---
// Consumiremos la API de Roommatik.
$router->before('GET|POST|PUT|DELETE', '/api/v1/webhook/.*', function() {
    (new WebhookAuthMiddleware())();
});

$router->get('/api/v1/webhook/health', function() {
    (new WebhookHealthController())->check();
});

$router->post('/api/v1/webhook/booking', function() {
    (new WebhookBookingController())->post();
});

$router->put('/api/v1/webhook/booking', function() {
    (new WebhookBookingController())->put();
});

$router->patch('/api/v1/webhook/booking', function() {
    (new WebhookBookingController())->patch();
});
