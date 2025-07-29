<?php
namespace App\helper;

use Firebase\JWT\JWT;

class JWTToken{
    public static function CreateToken($userEmail, $userId){
        $key = env('JWT_KEY');

        $payload = [
            'iss' => 'Laravel-token',
            'ait' => time(),
            'exp' => time() + 60 * 24 * 30,
            'userEmail' => $userEmail,
            'userId' => $userId,
        ];
        return JWT::encode($payload, $key, 'HS256');
    }// end function
}
