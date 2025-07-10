<?php

namespace App\Utils;

class CommonFormatUtils
{
    /**
     * Formatea una fecha de entrada YYYYMMDD a DD/MM/YYYY
     */
    public static function formatDateToSpanish($date)
    {
        if (!$date || strlen($date) !== 8) return '';
        return substr($date, 6, 2) . '/' . substr($date, 4, 2) . '/' . substr($date, 0, 4);
    }

    /**
     * Formatea una fecha de entrada YYYYMMDD a formato ISO 8601 (1978-10-18T23:00:00.000Z)
     */
    public static function formatDateToIso($date)
    {
        if (!$date || strlen($date) !== 8) return null;
        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);
        return $year . '-' . $month . '-' . $day . 'T23:00:00.000Z';
    }
}
