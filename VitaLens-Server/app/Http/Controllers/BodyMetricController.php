<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBodyMetricsRequest;
use App\Services\BodyMetricService;
use App\Services\EngineeredFeatureService;
use App\Services\RiskPredictionService;
use Illuminate\Http\Request;

class BodyMetricController extends Controller
{
    protected $bodyMetricService;
    protected $engineeredFeatureService;
    protected $riskPredictionService;

    public function __construct(
        BodyMetricService $bodyMetricService,
        EngineeredFeatureService $engineeredFeatureService,
        RiskPredictionService $riskPredictionService
    ) {
        $this->bodyMetricService = $bodyMetricService;
        $this->engineeredFeatureService = $engineeredFeatureService;
        $this->riskPredictionService = $riskPredictionService;
    }

    public function updateMetrics(UpdateBodyMetricsRequest $request)
    {
        try {
            $user = $request->user();
            $metrics = $request->validated();
            
            $this->bodyMetricService->addMetrics($user, $metrics);
            
            // auto trigger to recalculate features with new body metrics
            $this->engineeredFeatureService->prepareUserFeatures($user);
            
            // auto trigger to update risk predictions
            $this->riskPredictionService->predictUserRisks($user);
            
            return $this->responseJSON($metrics, "Body metrics updated and predictions refreshed successfully", 201);
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to update body metrics: " . $e->getMessage(), 500);
        }
    }

    public function getUserMetrics(Request $request)
    {
        try {
            $user = $request->user();
            $bodyMetrics = $user->bodyMetrics()
                ->with(['healthVariable', 'unit'])
                ->latest('created_at')
                ->take(2)
                ->get();
            
            $formatted = [];
            foreach ($bodyMetrics as $metric) {
                $formatted[$metric->healthVariable->key] = [
                    'value' => (float) $metric->value,
                    'unit' => $metric->unit->name,
                    'updated_at' => $metric->created_at
                ];
            }
            
            return $this->responseJSON($formatted, "Body metrics retrieved successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve body metrics: " . $e->getMessage(), 500);
        }
    }
}