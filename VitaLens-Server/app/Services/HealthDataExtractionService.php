<?php

namespace App\Services;

use App\Models\MedicalDocument;
use App\Models\HabitLog;
use App\Models\HealthVariable;
use App\Prompts\HealthExtractionPrompts;

class HealthDataExtractionService
{
    protected $aiService;
    protected $medicalMetricService;
    protected $habitMetricService;
    protected $ragIngestionService;

    public function __construct(
        AIService $aiService,
        MedicalMetricService $medicalMetricService,
        HabitMetricService $habitMetricService,
        RagIngestionService $ragIngestionService
    ) {
        $this->aiService = $aiService;
        $this->medicalMetricService = $medicalMetricService;
        $this->habitMetricService = $habitMetricService;
        $this->ragIngestionService = $ragIngestionService;
    }

    public function extractFromDocument(MedicalDocument $document): array
    {
        $documentText = $document->documentTexts()->first();
        
        if (!$documentText || !$documentText->extracted_text) {
            return [
                'success' => false,
                'message' => 'No extracted text found for this document',
            ];
        }

        // get all health variables with their units
        $healthVariables = HealthVariable::with('unit')->get()->all();

        // build prompt with text and variables
        $messages = HealthExtractionPrompts::documentExtractionPrompt(
            $documentText->extracted_text,
            $healthVariables
        );

        try {
            $aiResponse = $this->aiService->aiCall($messages);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        if (!$aiResponse) {
            return [
                'success' => false,
                'message' => 'AI service returned empty response',
            ];
        }

        $parsedResponse = json_decode($aiResponse, true);

        if (!$parsedResponse || !isset($parsedResponse['metrics'])) {
            return [
                'success' => false,
                'message' => 'Invalid AI response format: ' . $aiResponse,
            ];
        }

        $this->medicalMetricService->storeMetrics(
            $document->user_id,
            $document->id,
            $parsedResponse['metrics'],
            $parsedResponse['document_date'] ?? null
        );

        // update document date if found
        if (isset($parsedResponse['document_date']) && $parsedResponse['document_date']) {
            $document->update(['document_date' => $parsedResponse['document_date']]);
        }

        return [
            'success' => true,
            'message' => 'Metrics extracted successfully',
            'metrics_count' => count($parsedResponse['metrics']),
        ];
    }

    public function extractFromHabitLog(HabitLog $log): array
    {
        if (!$log->raw_text) {
            return [
                'success' => false,
                'message' => 'No text found in habit log',
            ];
        }

        // get only habit-related health variables
        $habitVariables = HealthVariable::whereIn('key', [
            'smoking_status',
            'alcohol_intake',
            'activity_moderate',
            'activity_vigorous',
            'sleep_duration'
        ])->with('unit')->get()->all();

        // fetch context for AI
        $user = $log->user;
        $topRisk = app(RiskPredictionService::class)->getUserTopRisk($user);
        $recentLogs = app(HabitLogService::class)->getRecentLogs($user, 7);

        // build prompt with habit text, variables, and context
        $messages = HealthExtractionPrompts::habitExtractionPrompt(
            $log->raw_text,
            $habitVariables,
            $topRisk,
            $recentLogs
        );

        try {
            $aiResponse = $this->aiService->aiCall($messages);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        if (!$aiResponse) {
            return [
                'success' => false,
                'message' => 'AI service returned empty response',
            ];
        }

        $parsedResponse = json_decode($aiResponse, true);

        if (!$parsedResponse || !isset($parsedResponse['metrics'])) {
            return [
                'success' => false,
                'message' => 'Invalid AI response format: ' . $aiResponse,
            ];
        }

        $this->habitMetricService->storeMetrics(
            $log->user_id,
            $log->id,
            $parsedResponse['metrics']
        );

        if (isset($parsedResponse['ai_insight'])) {
            $log->ai_insight = $parsedResponse['ai_insight'];
            $log->save();

            $this->ragIngestionService->ingestHabitLog($log);
        }

        return [
            'success' => true,
            'message' => 'Habit metrics and insight extracted successfully',
            'metrics_count' => count($parsedResponse['metrics']),
            'ai_insight' => $parsedResponse['ai_insight'] ?? null,
        ];
    }
}