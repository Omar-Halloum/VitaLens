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
        $user = Auth::user();
        $chatData = $this->chatService->getUserChat($user);

        return $this->responseJSON($chatData, "Chat retrieved successfully");
    }
    
    public function sendMessage(SendMessageRequest $request): JsonResponse
    {
        $user = Auth::user();
        $result = $this->chatService->sendMessage($user, $request->message);

        return $this->responseJSON($result, "Message sent successfully");
    }
}