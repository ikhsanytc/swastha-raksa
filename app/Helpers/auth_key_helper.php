<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Untuk membuat auth key dengan jwt
 */
function createAuthKey($uid)
{
    $key = getenv('JWT_SECRET');
    $payload = [
        'iat' => time(),
        'exp' => strtotime('+90 days'),
        'uid' => $uid,
    ];
    return JWT::encode($payload, $key, 'HS256');
}

/**
 * Untuk mengecek apakah auth key valid atau tidak
 */
function validateAuthKey($token)
{
    try {
        $key = getenv('JWT_SECRET');
        return JWT::decode($token, new Key($key, 'HS256'));
    } catch (Exception $e) {
        return null;
    }
}
