<?php

namespace App\Services;

class TokenUser
{
    private static $token;
    public static function set($token)
    {
        self::$token = $token;
    }
    public static function get()
    {
        return self::$token;
    }
}
