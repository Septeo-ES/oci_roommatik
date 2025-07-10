<?php

namespace App\Utils;
use App\Utils\CommonFormatUtils;

class GuestAcompanantesHelper
{
    /**
     * Devuelve el array de acompañantes a partir del campo temp_acompanantes (string o null)
     */
    public static function decodeAcompanantes($tempAcompanantes)
    {
        if (empty($tempAcompanantes)) return [];
        $arr = json_decode($tempAcompanantes, true);
        return is_array($arr) ? $arr : [];
    }

    /**
     * Devuelve true si el guestId ya existe en el array de acompañantes
     */
    public static function existsGuest($acompanantes, $guestId)
    {
        foreach ($acompanantes as $a) {
            if (isset($a['guestId']) && $a['guestId'] == $guestId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Añade un nuevo acompañante al array y devuelve el array actualizado
     */
    public static function addGuest($acompanantes, $guest)
    {
        $acompanantes[] = $guest;
        return $acompanantes;
    }

    /**
     * Utilidad para mapear nacionalidad (futuro)
     */
    public static function mapNacionalidad($nationality)
    {
        // TODO: Mapear nacionalidad a código/valor estándar
        return $nationality;
    }

    /**
     * Utilidad para mapear país (futuro)
     */
    public static function mapPais($country)
    {
        // TODO: Mapear país a código/valor estándar
        return $country;
    }

    /**
     * Utilidad para mapear sexo (futuro)
     */
    public static function mapSexo($gender)
    {
        // TODO: Mapear sexo a código/valor estándar
        return $gender;
    }

    /**
     * Utilidad para mapear tipo de documento (futuro)
     */
    public static function mapTipoDocumento($idTypeName)
    {
        // TODO: Mapear tipo de documento a código/valor estándar
        return $idTypeName;
    }

    /**
     * Utilidad para mapear tipo de pago (futuro)
     */
    public static function mapTipoPago($paymentType)
    {
        // TODO: Mapear tipo de pago a código/valor estándar
        return $paymentType;
    }

    /**
     * Devuelve true si la fecha de nacimiento corresponde a un menor de 14 años (incluido)
     * @param string $birthdate Fecha en formato YYYYMMDD
     * @return bool
     */
    public static function isMenorEdad($birthdate)
    {
        if (!$birthdate || strlen($birthdate) !== 8) return false;
        $year = (int)substr($birthdate, 0, 4);
        $month = (int)substr($birthdate, 4, 2);
        $day = (int)substr($birthdate, 6, 2);
        $fechaNacimiento = \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
        if (!$fechaNacimiento) return false;
        $hoy = new \DateTime();
        $edad = $hoy->diff($fechaNacimiento)->y;
        return $edad < 14;
    }

    /**
     * Mapea el guest del body al formato de acompañante con orden y variables fijas, añadiendo guestId
     */
    public static function mapGuestBodyToAcompanante($guest)
    {
        return [
            'guestId' => $guest['guestId'] ?? '',
            'nombre' => $guest['firstName'] ?? '',
            'apellidos' => $guest['lastName1'] ?? '',
            'codigoPostal' => $guest['zipCode'] ?? '',
            'direccion' => $guest['street'] ?? '',
            'email' => $guest['email'] ?? '',
            'fechaExpedicion' => CommonFormatUtils::formatDateToSpanish($guest['idIssueDate'] ?? ''),
            'fechaExpedicionDate' => CommonFormatUtils::formatDateToIso($guest['idIssueDate'] ?? ''),
            'fechaNacimiento' => CommonFormatUtils::formatDateToSpanish($guest['birthdate'] ?? ''),
            'fechaNacimientoDate' => CommonFormatUtils::formatDateToIso($guest['birthdate'] ?? ''),
            'fechaCaducidad' => CommonFormatUtils::formatDateToSpanish($guest['idExpirationDate'] ?? ''),
            'fechaCaducidadDate' => CommonFormatUtils::formatDateToIso($guest['idExpirationDate'] ?? ''),
            'pais' => self::mapPais($guest['country'] ?? ''), // TODO [CAMS-3327]: Mapear país a código/valor estándar
            'nacionalidad' => self::mapNacionalidad($guest['nationality'] ?? ''), // TODO [CAMS-3328]: Mapear nacionalidad a código/valor estándar
            'parentesco' => $guest['relationShip'] ?? '',
            'poblacion' => $guest['city'] ?? '',
            'sexo' => self::mapSexo($guest['gender'] ?? ''), // TODO [CAMS-3329]: Mapear sexo a código/valor estándar
            'tipoDocumento' => self::mapTipoDocumento($guest['idTypeName'] ?? ''), // TODO [CAMS-3330]: Mapear tipo de documento a código/valor estándar
            'indexSeleccionat' => $guest['indexSeleccionat'] ?? '0',
            'menor' => isset($guest['birthdate']) ? self::isMenorEdad($guest['birthdate']) : false
        ];
    }
}
