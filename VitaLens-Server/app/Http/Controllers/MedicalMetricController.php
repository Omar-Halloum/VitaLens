<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExtractMetricsRequest;
use App\Services\HealthDataExtractionService;
use App\Services\MedicalMetricService;
use App\Services\EngineeredFeatureService;
use App\Services\RiskPredictionService;
use App\Models\MedicalDocument;
use Illuminate\Http\Request;

class MedicalMetricController extends Controller
{
    protected $healthDataExtractionService;
    protected $medicalMetricService;
    protected $engineeredFeatureService;
    protected $riskPredictionService;

    public function __construct(
        HealthDataExtractionService $healthDataExtractionService,
        MedicalMetricService $medicalMetricService,
        EngineeredFeatureService $engineeredFeatureService,
        RiskPredictionService $riskPredictionService
    ) {
        $this->healthDataExtractionService = $healthDataExtractionService;
        $this->medicalMetricService = $medicalMetricService;
        $this->engineeredFeatureService = $engineeredFeatureService;
        $this->riskPredictionService = $riskPredictionService;
    }

    public function extractMetrics(ExtractMetricsRequest $request)
    {
        try {
            $document = MedicalDocument::findOrFail($request->document_id);
            
            $result = $this->healthDataExtractionService->extractFromDocument($document);
            
            if (!$result['success']) {
                return $this->responseJSON(null, $result['message'], 400);
            }

            // auto trigger to recalculate features with new medical metrics
            $features = $this->engineeredFeatureService->prepareUserFeatures($document->user);
            
            // auto trigger to update risk predictions
            $this->riskPredictionService->predictUserRisks($document->user, $features);

            return $this->responseJSON($result, "Metrics extracted and predictions updated successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to extract metrics: " . $e->getMessage(), 500);
        }
    }

    public function getUserMetrics(Request $request)
    {
        try {
            $user = $request->user();
            $metrics = $this->medicalMetricService->getMetricsByUser($user);
            
            return $this->responseJSON($metrics, "Metrics retrieved successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve metrics: " . $e->getMessage(), 500);
        }
    }

    public function getDocumentMetrics(Request $request, $documentId)
    {
        try {
            $metrics = $this->medicalMetricService->getMetricsByDocument($documentId);
            
            return $this->responseJSON($metrics, "Document metrics retrieved successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve document metrics: " . $e->getMessage(), 500);
        }
    }
}