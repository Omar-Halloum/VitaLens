<?php

namespace App\Prompts;

class RiskInsightPrompts
{
    public static function generateInsightPrompt(
        string $riskName,
        string $riskKey,
        float $probability,
        array $healthContext
    ): array
    {
        $contextText = "";
        if (!empty($healthContext)) {
            $contextText = "USER HEALTH DATA:\n";
            foreach ($healthContext as $key => $value) {
                $contextText .= "- " . str_replace('_', ' ', $key) . ": " . $value . "\n";
            }
            $contextText .= "\n";
        }

        $probabilityPercent = round($probability * 100);

        $userPrompt = "RISK ANALYSIS:\n";
        $userPrompt .= "Risk Type: {$riskName}\n";
        $userPrompt .= "Probability: {$probabilityPercent}%\n\n";
        $userPrompt .= $contextText;
        
        $userPrompt .= "INSTRUCTIONS:\n
                        Based on the risk level and health data above, generate a short, personalized health insight.
                        - If probability > 40%: Warning tone. Explain contributing factors and give one specific, actionable step to reduce risk.
                        - If probability <= 40%: Supportive tone. Congratulate the user and suggest some advice to maintain this good status.
                        - Keep it under 3 sentences.
        
                        RESPOND WITH THIS JSON:\n{\n    \"insight\": \"Your response here\"\n}";

        return [
            ['role' => 'user', 'content' => $userPrompt],
        ];
    }
}