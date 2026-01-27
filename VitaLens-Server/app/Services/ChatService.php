<?php

namespace App\Services;

use App\Models\User;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Prompts\ChatPrompts;
use Illuminate\Support\Facades\Http;

class ChatService
{
    protected $aiService;
    protected $ragUrl;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
        $this->ragUrl = config('services.vitalens_intelligence.base_url');
    }

    public function getUserChat(User $user)
    {
        $chat = $user->chat()->with('messages')->first();
        
        if (!$chat) {
            $chat = new Chat;
            $chat->user_id = $user->id;
            $chat->save();
        }

        return $chat;
    }

    public function sendMessage(User $user, string $message): array
    {
        $chat = $this->getUserChat($user);

        $userMessageRecord = new ChatMessage;
        $userMessageRecord->chat_id = $chat->id;
        $userMessageRecord->role = 'user';
        $userMessageRecord->content = $message;
        $userMessageRecord->save();

        try {
            $context = $this->queryRag($user->id, $message);

            $aiResponse = $this->generateResponse($message, $context);

            $aiMessage = new ChatMessage;
            $aiMessage->chat_id = $chat->id;
            $aiMessage->role = 'assistant';
            $aiMessage->content = $aiResponse;
            $aiMessage->save();

            return [
                'success' => true,
                'message' => $aiMessage->content,
            ];

        } catch (\Exception $e) {
            $errorMessage = "I'm having trouble accessing your health data right now. Please try again.";
            $errorRecord = new ChatMessage;
            $errorRecord->chat_id = $chat->id;
            $errorRecord->role = 'assistant';
            $errorRecord->content = $errorMessage;
            $errorRecord->save();

            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        }
    }

    protected function queryRag(int $userId, string $query): array
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(30)->post("{$this->ragUrl}/rag/query", [
                'user_id' => $userId,
                'query' => $query,
                'n_results' => 15,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['results'] ?? [];
            }

            return [];

        } catch (\Exception $e) {
            return [];
        }
    }

    protected function generateResponse(string $userMessage, array $context): string
    {
        $prompt = ChatPrompts::chatPrompt($userMessage, $context);
        
        $aiResponse = $this->aiService->aiCall($prompt);
        $decoded = json_decode($aiResponse, true);

        return $decoded['response'] ?? "I couldn't generate a response. Please try again.";
    }
}