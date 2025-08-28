<?php

namespace App\Filters;

use App\Libraries\JWTService;
use App\Models\TokenModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class JWTAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return service('response')
                ->setJSON(['message' => 'Missing token'])
                ->setStatusCode(401);
        }

        $token = substr($authHeader, 7);
        $jwt = new JWTService();

        try {
            $payload = $jwt->decode($token);
        } catch (\Throwable $e) {
            return service('response')
                ->setJSON(['message' => 'Invalid/expired token'])
                ->setStatusCode(401);
        }

        $tokenModel = new TokenModel();
        $found = $tokenModel->where('access_token', $token)->first();

        if (!$found) {
            return service('response')
                ->setJSON(['message' => 'Token revoked'])
                ->setStatusCode(401);
        }

        return $request->user = [
            'id' => $payload['uid'] ?? null,
            'role' => $payload['role'] ?? 'user',
        ];
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }
}