<?php

namespace App\Controllers;

use App\Libraries\JWTService;
use App\Models\TokenModel;
use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class AuthController extends ResourceController
{
    protected $format = 'json';

    public function register()
    {
        $rules = [
            'name' => 'required|min_length[2]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);
        $userModel = new UserModel();

        $id = $userModel->insert([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);

        return $this->respondCreated(['id' => $id]);
    }

    public function login()
    {
        $data = $this->request->getJSON(true);
        $userModel = new UserModel();

        $user = $userModel->where('email', $data['email'] ?? '')->first();

        if (!$user || !password_verify($data['password'] ?? '', $user['password'])) {
            return $this->failUnauthorized('Email hoặc mật khẩu không đúng');
        }

        $jwt = new JWTService();
        $token = $jwt->issue(['uid' => $user['id'], 'role' => $user['role']]);

        $refreshToken = bin2hex(random_bytes(40));
        $expiresAt = date('Y-m-d H:i:s', time() + (int)env('JWT_TTL', 3600));

        (new TokenModel())->insert([
            'user_id' => $user['id'],
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'ip' => $this->request->getIPAddress(),
            'device' => $this->request->getUserAgent()->getBrowser() . ' ' . $this->request->getUserAgent()->getVersion(),
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->respond([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt,
        ]);
    }

    public function refresh()
    {
        $data = $this->request->getJSON(true);
        $refresh = $data['refresh_token'] ?? null;

        if (!$refresh) {
            return $this->failValidationErrors(['refresh_token' => 'required']);
        }

        $tokenModel = new TokenModel();
        $row = $tokenModel->where('refresh_token', $refresh)->first();

        if (!$row) {
            return $this->failUnauthorized('Refresh token không hợp lệ');
        }

        $jwt = new JWTService();
        $payload = ['uid' => $row['user_id']];
        $newAccess = $jwt->issue($payload);

        $tokenModel->update($row['id'], [
            'access_token' => $newAccess,
            'expires_at' => date('Y-m-d H:i:s', time() + (int)env('JWT_TTL', 3600))
        ]);

        return $this->respond(['access_token' => $newAccess]);
    }

    public function logout()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            $tokenModel = new TokenModel();
            $tokenModel->where('access_token', $token)->delete();
        }

        return $this->respond(['message' => 'Logged out']);
    }
}
