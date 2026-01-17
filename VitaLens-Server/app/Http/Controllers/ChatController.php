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
        $chat = $this->chatService->getUserChat($user);

        return response()->json([
            'chat' => [
                'id' => $chat->id,
                'messages' => $chat->messages->map(function ($msg) {
                    return [
                        'id' => $msg->id,
                        'role' => $msg->role,
                        'content' => $msg->content,
                        'created_at' => $msg->created_at->toISOString(),
                    ];
                }),
            ],
        ]);
    }
    
    public function sendMessage(SendMessageRequest $request): JsonResponse
    {
        $user = Auth::user();
        $result = $this->chatService->sendMessage($user, $request->message);

        return response()->json($result);
    }
}