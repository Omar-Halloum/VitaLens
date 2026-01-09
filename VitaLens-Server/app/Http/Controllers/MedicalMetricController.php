<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExtractMetricsRequest;
use App\Services\HealthDataExtractionService;
use App\Services\MedicalMetricService;
use App\Models\MedicalDocument;
use Illuminate\Http\Request;

class MedicalMetricController extends Controller
{
    protected $healthDataExtractionService;
    protected $medicalMetricService;

    public function __construct(
        HealthDataExtractionService $healthDataExtractionService,
        MedicalMetricService $medicalMetricService
    ) {
        $this->healthDataExtractionService = $healthDataExtractionService;
        $this->medicalMetricService = $medicalMetricService;
    }

    public function extractMetrics(ExtractMetricsRequest $request)
    {
        try {
            $document = MedicalDocument::findOrFail($request->document_id);
            
            $result = $this->healthDataExtractionService->extractFromDocument($document);
            
            if (!$result['success']) {
                return $this->responseJSON(null, $result['message'], 400);
            }

            return $this->responseJSON($result, $result['message']);
            
        } catch (\Exception $e) {
            return $this->responseJSON($e->getMessage(), "failure", 500);
        }
    }

    public function getUserMetrics(Request $request)
    {
        try {
            $user = $request->user();
            $metrics = $this->medicalMetricService->getMetricsByUser($user);
            
            return $this->responseJSON($metrics, "Metrics retrieved successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON($e->getMessage(), "failure", 500);
        }
    }

    public function getDocumentMetrics(Request $request, $documentId)
    {
        try {
            $metrics = $this->medicalMetricService->getMetricsByDocument($documentId);
            
            return $this->responseJSON($metrics, "Document metrics retrieved successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON($e->getMessage(), "failure", 500);
        }
    }
}

