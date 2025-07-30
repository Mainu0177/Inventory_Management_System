<?php
namespace App\helper;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

    public static function VerifyToken($token){
        try {
            if($token === null){
                return 'unauthorized';
            }else{
                $key = env('JWT_KEY');
                $decode = JWT::decode($token, new Key($key, 'HS256'));
                return $decode;
            }
        } catch (\Exception $e) {
            return 'unauthorized';
        }
    }// end function

    public static function CreateTokenForSetPassword($userEmail):string{
        $key = env('JWT_KEY');

        $payload = [
            'iss' => 'Laravel-token',
            'ait' => time(),
            'exp' => time() + 60 * 24 * 30,
            'userEmail' => $userEmail,
            'userId' => 0,
        ];
        return JWT::encode($payload, $key, 'HS256');
    }// end function
}
