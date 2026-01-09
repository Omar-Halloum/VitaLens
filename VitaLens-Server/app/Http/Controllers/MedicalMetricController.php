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
}

