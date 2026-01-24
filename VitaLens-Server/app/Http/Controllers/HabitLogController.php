<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHabitLogRequest;
use App\Services\HabitLogService;
use App\Services\HabitMetricService;
use App\Services\HealthDataExtractionService;
use App\Services\EngineeredFeatureService;
use App\Services\RiskPredictionService;
use Illuminate\Http\Request;

class HabitLogController extends Controller
{
    protected $habitLogService;
    protected $habitMetricService;
    protected $healthDataExtractionService;
    protected $engineeredFeatureService;
    protected $riskPredictionService;

    public function __construct(
        HabitLogService $habitLogService,
        HabitMetricService $habitMetricService,
        HealthDataExtractionService $healthDataExtractionService,
        EngineeredFeatureService $engineeredFeatureService,
        RiskPredictionService $riskPredictionService
    ) {
        $this->habitLogService = $habitLogService;
        $this->habitMetricService = $habitMetricService;
        $this->healthDataExtractionService = $healthDataExtractionService;
        $this->engineeredFeatureService = $engineeredFeatureService;
        $this->riskPredictionService = $riskPredictionService;
    }

    public function storeHabit(StoreHabitLogRequest $request)
    {
        try {
            $user = $request->user();
            $habitText = $request->input('habit_text');
            
            $habitLog = $this->habitLogService->createLog($user, $habitText);
            
            $extractionResult = $this->healthDataExtractionService->extractFromHabitLog($habitLog);
            
            if (!$extractionResult['success']) {
                return $this->responseJSON(null, $extractionResult['message'], 400);
            }
            
            // auto trigger to recalculate features with new habit metrics
            $features = $this->engineeredFeatureService->prepareUserFeatures($user);
            
            // auto trigger to update risk predictions
            $this->riskPredictionService->predictUserRisks($user, $features);
            
            return $this->responseJSON([
                'habit_log_id' => $habitLog->id,
                'metrics_count' => $extractionResult['metrics_count'],
                'ai_insight' => $extractionResult['ai_insight'] ?? null,
            ], "Habit logged, metrics extracted, and predictions updated successfully", 201);
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to log habit: " . $e->getMessage(), 500);
        }
    }

    public function getUserLogs(Request $request)
    {
        try {
            $user = $request->user();
            $logs = $this->habitLogService->getUserLogs($user);
            
            return $this->responseJSON($logs, "Habit logs retrieved successfully");
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve habit logs: " . $e->getMessage(), 500);
        }
    }

    public function getUserHabitMetrics(Request $request)
    {
        try {
            $user = $request->user();
            $metrics = $this->habitMetricService->getMetricsByUser($user);
            
            return $this->responseJSON($metrics, "Habit metrics retrieved successfully");
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve habit metrics: " . $e->getMessage(), 500);
        }
    }

    public function getLogMetrics(Request $request, $logId)
    {
        try {
            $metrics = $this->habitMetricService->getMetricsByLog($logId);
            
            return $this->responseJSON($metrics, "Log metrics retrieved successfully");
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve log metrics: " . $e->getMessage(), 500);
        }
    }
}