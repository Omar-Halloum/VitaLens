<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): array
    {

        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->gender = $data['gender'];
        $user->birth_date = $data['birth_date'];
        
        $user->save();

        $token = Auth::login($user);

        return [
            'user'  => $user,
            'token' => $token
        ];
    }

    public function login(array $credentials): ?array
    {
        if (!$token = Auth::attempt($credentials)) {
            return null;
        }

        return [
            'user'  => Auth::user(),
            'token' => $token
        ];
    }

    public function logout(): void
    {
        Auth::logout();
    }
    
    public function refresh(): array
    {
        return [
            'user'  => Auth::user(),
            'token' => Auth::refresh(),
        ];
    }
}