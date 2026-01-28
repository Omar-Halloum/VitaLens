<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request){
        try {
            $result = $this->authService->register($request->validated());

            $user = $result['user'];
            $user->token = $result['token'];

            return $this->responseJSON($user, "User created successfully", 201);
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to register user: " . $e->getMessage(), 500);
        }
    }

    public function login(LoginRequest $request){
        try {
            $credentials = $request->validated();
            
            $result = $this->authService->login($credentials);

            if (!$result) {
                return $this->responseJSON(null, "Invalid credentials", 401);
            }

            $user = $result['user'];
            $user->token = $result['token'];

            return $this->responseJSON($user, "Login successful");
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to login: " . $e->getMessage(), 500);
        }
    }

    public function logout(){
        try {
            $this->authService->logout();
            return $this->responseJSON(null, "Successfully logged out");
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to logout: " . $e->getMessage(), 500);
        }
    }

    public function refresh(){
        try {
            $result = $this->authService->refresh();
            
            $payload = [
                'user' => $result['user'],
                'authorisation' => [
                    'token' => $result['token'],
                    'type'  => 'bearer',
                ]
            ];

            return $this->responseJSON($payload, "Token refreshed");
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to refresh token: " . $e->getMessage(), 500);
        }
    }

    public function displayError(): JsonResponse
    {
        return $this->responseJSON('Unauthorized', 'failure', 401);
    }
}