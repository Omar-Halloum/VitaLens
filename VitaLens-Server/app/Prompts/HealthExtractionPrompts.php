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

