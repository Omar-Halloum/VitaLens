<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ClinicApiKey;
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

        if ($apiKey->user) {
            Auth::login($apiKey->user);
        } else {
             return $this->responseJSON(null, 'Unauthorized: Invalid User for API Key', 401);
        }

        return $next($request);
    }
}
