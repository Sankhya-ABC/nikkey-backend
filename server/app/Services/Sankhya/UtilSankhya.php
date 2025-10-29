<?php

namespace App\Services\Sankhya;

class UtilSankhya
{
    public static function message($status, $read)
    {
        $message = ($status == 1 || $status == 2) ? 'OK' : (string) $read->statusMessage;

        return $message;
    }
}
