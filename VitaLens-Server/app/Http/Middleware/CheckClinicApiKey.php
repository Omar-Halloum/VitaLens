<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ClinicApiKey;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseTrait;

class CheckClinicApiKey
{
    use ResponseTrait;
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->responseJSON(null, 'Unauthorized: No API Key provided', 401);
        }

        $apiKey = ClinicApiKey::where('key', $token)->first();

        if (!$apiKey) {
            return $this->responseJSON(null, 'Unauthorized: Invalid API Key', 401);
        }

        $systemAdmin = User::find(1);
        if ($systemAdmin) {
            Auth::login($systemAdmin);
        } else {
            return $this->responseJSON(null, 'Unauthorized: System Admin not found', 401);
        }

        return $next($request);
    }
}
