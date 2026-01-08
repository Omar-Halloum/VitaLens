<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\BodyMetricService;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected $bodyMetricService;

    public function __construct(BodyMetricService $bodyMetricService)
    {
        $this->bodyMetricService = $bodyMetricService;
    }

    public function register(array $data): array
    {

        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->gender = $data['gender'];
        $user->birth_date = $data['birth_date'];
        
        $user->save();

        $userFields = ['name', 'email', 'password', 'gender', 'birth_date'];

        // emove user fields, leaving only potential metrics
        $metricsToLog = array_diff_key($data, array_flip($userFields));

        if (!empty($metricsToLog)) {
            $this->bodyMetricService->addMetrics($user, $metricsToLog);
        }

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