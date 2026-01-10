<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    protected $apiKey;
    protected $model;
    protected $baseUrl;
    protected $timeout;

    public function __construct()
    {
        $this->apiKey = config('ai.api_key');
        $this->model = config('ai.model');
        $this->baseUrl = config('ai.base_url');
        $this->timeout = config('ai.timeout');
    }

    public function aiCall(array $messages)
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0,
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($response->failed()) {
                $error = $response->json();
                throw new \Exception('AI API Error: ' . ($error['error']['message'] ?? $response->body()));
            }

            return $response->json('choices.0.message.content');

        } catch (\Exception $e) {
            throw new \Exception('AI Service Error: ' . $e->getMessage());
        }
    }
}
