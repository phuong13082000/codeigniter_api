<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{
    protected string $secret;
    protected int $ttl;


    public function __construct()
    {
        $this->secret = env('JWT_SECRET');
        $this->ttl = (int)env('JWT_TTL', 3600);
    }


    public function issue(array $payload): string
    {
        $now = time();
        $data = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $this->ttl,
        ]);
        return JWT::encode($data, $this->secret, 'HS256');
    }


    public function decode(string $token): array
    {
        return (array)JWT::decode($token, new Key($this->secret, 'HS256'));
    }
}