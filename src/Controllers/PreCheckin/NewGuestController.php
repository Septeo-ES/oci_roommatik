<?php

namespace App\Controllers\PreCheckin;

use App\Models\Database;
use App\Models\LogApi;
use App\Utils\GuestAcompanantesHelper;

class NewGuestController
{
    public function post()
    {
        $statusCode = 200;
        $response = null;
        $body = json_decode(file_get_contents('php://input'), true);
        $reservationId = $body['reservationId'] ?? null;
        $guest = $body['guest'] ?? null;
        $db = Database::getInstance()->getConnection();

        if (!$reservationId || !$guest) {
            $statusCode = 400;
            $response = [
                'returnCode' => 1,
                'description' => 'Faltan datos obligatorios (reservationId o guest) en el body.'
            ];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create('/api/v1/newguest', $_SERVER, $response, $statusCode);
            return;
        }

        // Buscar localizador
        $stmt = $db->prepare('SELECT localizador FROM reserva_localizador WHERE id_reserva = ?');
        $stmt->execute([$reservationId]);
        $row = $stmt->fetch();
        if (!$row) {
            $statusCode = 404;
            $response = [
                'returnCode' => 2,
                'description' => 'No existe checkin para esta reserva.'
            ];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create('/api/v1/newguest', $_SERVER, $response, $statusCode);
            return;
        }
        $localizador = $row['localizador'];

        // Buscar reserva
        $stmt = $db->prepare('SELECT realizacion, descarga, validacion, anulacion, temp_acompanantes FROM reserva WHERE localizador = ?');
        $stmt->execute([$localizador]);
        $reserva = $stmt->fetch();
        if (!$reserva) {
            $statusCode = 404;
            $response = [
                'returnCode' => 3,
                'description' => 'No existe checkin para esta reserva.'
            ];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create('/api/v1/newguest', $_SERVER, $response, $statusCode);
            return;
        }

        // Validaciones de estado
        if (!empty($reserva['anulacion'])) {
            $fecha = $reserva['anulacion'];
            $fechaFmt = substr($fecha,6,2).'/'.substr($fecha,4,2).'/'.substr($fecha,0,4);
            $statusCode = 409;
            $response = [
                'returnCode' => 4,
                'description' => 'El checkin ha sido anulado',
                'anulacion' => $fechaFmt
            ];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create('/api/v1/newguest', $_SERVER, $response, $statusCode);
            return;
        }
        if (!empty($reserva['validacion'])) {
            $fecha = $reserva['validacion'];
            $fechaFmt = substr($fecha,6,2).'/'.substr($fecha,4,2).'/'.substr($fecha,0,4);
            $statusCode = 409;
            $response = [
                'returnCode' => 5,
                'description' => 'El checkin ha sido validado ya por el PMS',
                'validacion' => $fechaFmt
            ];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create('/api/v1/newguest', $_SERVER, $response, $statusCode);
            return;
        }

        // Procesar acompañantes
        $acompanantes = GuestAcompanantesHelper::decodeAcompanantes($reserva['temp_acompanantes']);
        $guestId = $guest['guestId'] ?? null;
        if ($guestId && GuestAcompanantesHelper::existsGuest($acompanantes, $guestId)) {
            $statusCode = 409;
            $response = [
                'returnCode' => 6,
                'description' => 'El Guest ya existe.'
            ];
            http_response_code($statusCode);
            echo json_encode($response);
            LogApi::create('/api/v1/newguest', $_SERVER, $response, $statusCode);
            return;
        }
        // Añadir el nuevo guest
        $nuevoAcompanante = GuestAcompanantesHelper::mapGuestBodyToAcompanante($guest);
        $acompanantes = GuestAcompanantesHelper::addGuest($acompanantes, $nuevoAcompanante);
        $stmt = $db->prepare('UPDATE reserva SET temp_acompanantes = ? WHERE localizador = ?');
        $ok = $stmt->execute([json_encode($acompanantes), $localizador]);
        if ($ok) {
            $statusCode = 200;
            $response = [
                'returnCode' => 0,
                'description' => 'Success',
                'result' => [ 'guestId' => $guestId ],
                'pmsMessage' => null,
                'userMessage' => null
            ];
        } else {
            $statusCode = 500;
            $response = [
                'returnCode' => 99,
                'description' => 'Error al actualizar los acompañantes.'
            ];
        }
        http_response_code($statusCode);
        echo json_encode($response);
        LogApi::create('/api/v1/newguest', $_SERVER, $response, $statusCode);
    }
}
