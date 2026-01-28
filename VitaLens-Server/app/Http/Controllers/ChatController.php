<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function getChat(): JsonResponse
    {
        try {
            $user = Auth::user();
            $chatData = $this->chatService->getUserChat($user);

            return $this->responseJSON($chatData, "Chat retrieved successfully");
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve chat: " . $e->getMessage(), 500);
        }
    }
    
    public function sendMessage(SendMessageRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $result = $this->chatService->sendMessage($user, $request->message);

            return $this->responseJSON($result, "Message sent successfully");
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to send message: " . $e->getMessage(), 500);
        }
    }
}
