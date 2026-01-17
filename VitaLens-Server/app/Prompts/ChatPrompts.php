<?php

namespace App\Prompts;

class ChatPrompts
{
    public static function chatPrompt(string $userMessage, array $ragContext): array
    {
        $contextText = "";
        if (!empty($ragContext)) {
            $contextText = "RELEVANT INFORMATION FROM USER'S HEALTH RECORDS:\n\n";
            
            foreach ($ragContext as $chunk) {
                $text = is_array($chunk) ? ($chunk['text'] ?? '') : $chunk;
                if ($text) {
                    $contextText .= "- " . $text . "\n";
                }
            }
            $contextText .= "\n";
        }

        $systemPrompt = "You are a health assistant for VitaLens. Your role is to answer user questions based ONLY on their health data provided below.

                        RULES:
                        1. Only use information from the context provided
                        2. Be specific and cite dates when relevant
                        3. If the context doesn't contain the answer, say: 'I don't have that information in your records yet.'
                        4. Be encouraging and supportive
                        5. Do not make medical diagnoses or give medical advice
                        6. Return ONLY valid JSON with 'response' field";

        $userPrompt = "{$contextText}USER QUESTION:\n{$userMessage}\n\nRESPOND WITH THIS EXACT JSON FORMAT:\n{\n    \"response\": \"Your answer based on the context above\"\n}";

        return [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];
    }
}