<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateProfileRequest;

use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();
            $userWithMetrics = $this->userService->getProfile($user);
            
            return $this->responseJSON($userWithMetrics, "User profile retrieved successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve profile: " . $e->getMessage(), 500);
        }
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $user = $request->user();
            $data = $request->validated();

            $this->userService->updateProfile($user, $data);

            return $this->responseJSON($user, "Profile updated and health analysis triggered successfully");

        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to update profile: " . $e->getMessage(), 500);
        }
    }
}