<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest; // Uses your custom request validation
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(AuthRequest $request){

        $result = $this->authService->register($request->validated());

        $user = $result['user'];
        $user->token = $result['token'];

        // 3. Response handled by Trait using $this->
        return $this->responseJSON($user, "User created successfully", 201);
    }

    public function login(Request $request){

        $credentials = $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        $result = $this->authService->login($credentials);

        if (!$result) {
            return $this->responseJSON(null, "Unauthorized", 401);
        }

        $user = $result['user'];
        $user->token = $result['token'];

        return $this->responseJSON($user, "Login successful");
    }

    public function logout(){

        $this->authService->logout();
        return $this->responseJSON(null, "Successfully logged out");
    }

    public function refresh(){
        
        $result = $this->authService->refresh();
        
        $payload = [
            'user' => $result['user'],
            'authorisation' => [
                'token' => $result['token'],
                'type'  => 'bearer',
            ]
        ];

        return $this->responseJSON($payload, "Token refreshed");
    }
}