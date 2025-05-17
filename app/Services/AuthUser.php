<?php

namespace App\Services;

class AuthUser
{
    private static $user;
    public static function set($data)
    {
        self::$user = $data;
    }
    public static function get()
    {
        return self::$user;
    }
}
