<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();
            $user->load('userType');
            
            return $this->responseJSON($user, "User profile retrieved successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve profile: " . $e->getMessage(), 500);
        }
    }
}