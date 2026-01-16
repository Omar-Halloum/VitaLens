<?php

namespace App\Prompts;

class HealthExtractionPrompts
{
    public static function getSystemPrompt(): string
    {
        return "You are a medical data extraction assistant. Your job is to extract health metrics from medical documents (lab reports, test results).

                RULES:
                1. Only extract values that are clearly present in the text
                2. Convert all values to the units specified in the health variables list
                3. Do not guess or estimate values
                4. Extract the document date if visible (format: YYYY-MM-DD)
                5. Return ONLY valid JSON, no explanations
                6. Distinction: Extract the PATIENT RESULT, not the reference range/interval.

                If a value uses different units than specified, convert it:
                - Glucose: mmol/L to mg/dL → multiply by 18
                - Cholesterol: mmol/L to mg/dL → multiply by 38.67
                - Weight: lbs to kg → divide by 2.205
                - Height: inches to cm → multiply by 2.54";
    }

    protected static function getHabitSystemPrompt(): string
    {
        return "You are a health habit extraction assistant. Your job is to extract lifestyle and behavioral health metrics from user's daily habit logs.

                RULES:
                1. Only extract values that are clearly mentioned in the text
                2. Convert all values to the units specified in the health variables list
                3. Do not guess or estimate values
                4. Return ONLY valid JSON, no explanations
                5. Be flexible with language (e.g., 'jogged' = moderate activity, 'slept' = sleep duration)

                Unit conversions if needed:
                - Activity: hours to minutes → multiply by 60
                - Sleep: minutes to hours → divide by 60";
    }

    public static function documentExtractionPrompt(string $extractedText, array $healthVariables): array
    {
        $variablesList = self::formatVariablesForPrompt($healthVariables);

        $userPrompt = "Extract health metrics from this medical document.

                    HEALTH VARIABLES TO LOOK FOR:
                    {$variablesList}
                        
                    DOCUMENT TEXT:
                    {$extractedText}
                        
                    RESPOND WITH THIS EXACT JSON FORMAT:
                    {
                        \"document_date\": \"YYYY-MM-DD or null if not found\",
                        \"metrics\": [
                            {\"key\": \"variable_key\", \"value\": numeric_value},
                            {\"key\": \"variable_key\", \"value\": numeric_value}
                        ]
                    }
                        
                    Only include metrics that are clearly present in the document. Use the exact 'key' from the health variables list.";

        return [
            ['role' => 'system', 'content' => self::getSystemPrompt()],
            ['role' => 'user', 'content' => $userPrompt],
        ];
    }

    public static function habitExtractionPrompt(
        string $habitText, 
        array $habitVariables,
        ?array $topRisk = null,
        array $recentLogs = []
    ): array
    {
        $variablesList = self::formatVariablesForPrompt($habitVariables);

        $contextSection = "";
        
        if ($topRisk) {
            $riskName = $topRisk['display_name'];
            $probability = round($topRisk['probability'] * 100);
            $contextSection .= "\nUSER'S TOP HEALTH RISK:\n";
            $contextSection .= "- {$riskName}: {$probability}% probability\n";
            $contextSection .= "- Mention how today's habits may affect this specific risk.\n";
        }
        
        if (!empty($recentLogs)) {
            $contextSection .= "\nPREVIOUS HABIT LOGS (for trend comparison):\n";
            foreach ($recentLogs as $log) {
                $metricsText = [];
                foreach ($log['metrics'] as $key => $value) {
                    $metricsText[] = "{$key}: {$value}";
                }
                $contextSection .= "- {$log['date']}: " . implode(", ", $metricsText) . "\n";
            }
            $contextSection .= "- Compare today's metrics with previous days to identify improving or declining trends.\n";
        }

        $userPrompt = "Extract health habit metrics from this user's daily log entry AND generate a personalized AI insight.

                    HEALTH HABITS TO LOOK FOR:
                    {$variablesList}
                    {$contextSection}
                        
                    USER'S HABIT LOG:
                    {$habitText}
                        
                    RESPOND WITH THIS EXACT JSON FORMAT:
                    {
                        \"metrics\": [
                            {\"key\": \"variable_key\", \"value\": numeric_value},
                            {\"key\": \"variable_key\", \"value\": numeric_value}
                        ],
                        \"ai_insight\": \"Your personalized health insight based on the log, trends, and top risk. Be specific, actionable, and encouraging. Mention the user's top risk if provided.\"
                    }
                        
                    IMPORTANT NOTES:
                    - For smoking_status: 1 = smoker (smoked 100+ cigarettes in life), 2 = non-smoker
                    - For alcohol_intake: number of drinks consumed
                    - For activities: extract duration in minutes
                    - For sleep: extract duration in hours
                    - Only include habits that are clearly mentioned
                    - Use the exact 'key' from the health habits list
                    - The ai_insight should be 2-3 sentences, friendly but professional
                    - If previous logs show a trend (improving/declining), mention it
                    - If a top risk is provided, explain how today's habits relate to it";

        return [
            ['role' => 'system', 'content' => self::getHabitSystemPrompt()],
            ['role' => 'user', 'content' => $userPrompt],
        ];
    }

    protected static function formatVariablesForPrompt(array $healthVariables): string
    {
        $lines = [];

        foreach ($healthVariables as $variable) {
            $unitName = $variable->unit->name ?? 'N/A';
            $lines[] = "- {$variable->key}: {$variable->display_name} (Unit: {$unitName})";
        }

        return implode("\n", $lines);
    }
}