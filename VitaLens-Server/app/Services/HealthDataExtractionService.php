<?php

namespace App\Services;

use App\Models\MedicalDocument;
use App\Models\HealthVariable;
use App\Prompts\HealthExtractionPrompts;

class HealthDataExtractionService
{
    protected $aiService;
    protected $medicalMetricService;
    protected $medicalDocumentService;

    public function __construct(
        AIService $aiService,
        MedicalMetricService $medicalMetricService,
        MedicalDocumentService $medicalDocumentService
    ) {
        $this->aiService = $aiService;
        $this->medicalMetricService = $medicalMetricService;
        $this->medicalDocumentService = $medicalDocumentService;
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

        $aiResponse = $this->aiService->aiCall($messages);

        if (!$aiResponse) {
            return [
                'success' => false,
                'message' => 'AI service failed to respond',
            ];
        }

        $parsedResponse = json_decode($aiResponse, true);

        if (!$parsedResponse || !isset($parsedResponse['metrics'])) {
            return [
                'success' => false,
                'message' => 'Invalid AI response format',
            ];
        }

        $this->medicalMetricService->storeMetrics(
            $document->user_id,
            $document->id,
            $parsedResponse['metrics'],
            $parsedResponse['document_date'] ?? null
        );

        // Update document date if found
        if (isset($parsedResponse['document_date']) && $parsedResponse['document_date']) {
            $this->medicalDocumentService->updateDocumentDate(
                $document,
                $parsedResponse['document_date']
            );
        }

        return [
            'success' => true,
            'message' => 'Metrics extracted successfully',
            'metrics_count' => count($parsedResponse['metrics']),
        ];
    }
}

