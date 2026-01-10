<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetRiskPredictionRequest;
use App\Http\Requests\StorePredictionsRequest;
use App\Services\RiskPredictionService;
use App\Models\User;
use Illuminate\Http\Request;

class RiskPredictionController extends Controller
{
    protected $riskPredictionService;

    public function __construct(RiskPredictionService $riskPredictionService)
    {
        $this->riskPredictionService = $riskPredictionService;
    }

    public function predictRisks(Request $request)
    {
        try {
            $user = $request->user();
            $result = $this->riskPredictionService->predictUserRisks($user);
            
            if (!$result['success']) {
                return $this->responseJSON(null, $result['message'], 400);
            }
            
            return $this->responseJSON($result, $result['message']);
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to predict risks: " . $e->getMessage(), 500);
        }
    }

    public function getUserPredictions(Request $request)
    {
        try {
            $user = $request->user();
            $predictions = $this->riskPredictionService->getUserPredictions($user);
            
            return $this->responseJSON($predictions, "Risk predictions retrieved successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve predictions: " . $e->getMessage(), 500);
        }
    }

    public function getRiskPrediction(GetRiskPredictionRequest $request, string $riskKey)
    {
        try {
            $user = $request->user();
            $prediction = $this->riskPredictionService->getRiskPrediction($user, $riskKey);
            
            if (!$prediction) {
                return $this->responseJSON(null, "Risk prediction not found", 404);
            }
            
            return $this->responseJSON($prediction, "Risk prediction retrieved successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve prediction: " . $e->getMessage(), 500);
        }
    }

    public function checkDataSufficiency(Request $request)
    {
        try {
            $user = $request->user();
            $sufficiency = $this->riskPredictionService->checkDataSufficiency($user);
            
            return $this->responseJSON($sufficiency, "Data sufficiency checked successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to check data sufficiency: " . $e->getMessage(), 500);
        }
    }

    public function getRiskFactors(Request $request, string $riskKey)
    {
        try {
            $factors = $this->riskPredictionService->getRiskFactors($riskKey);
            
            if (empty($factors)) {
                return $this->responseJSON(null, "Risk type not found", 404);
            }
            
            return $this->responseJSON($factors, "Risk factors retrieved successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve risk factors: " . $e->getMessage(), 500);
        }
    }

    public function getRiskHistory(Request $request, ?string $riskKey = null)
    {
        try {
            $user = $request->user();
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            
            $history = $this->riskPredictionService->getRiskHistory(
                $user, 
                $riskKey,
                $startDate,
                $endDate
            );
            
            return $this->responseJSON($history, "Risk history retrieved successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve risk history: " . $e->getMessage(), 500);
        }
    }

    public function storePredictions(StorePredictionsRequest $request)
    {
        try {
            $user = User::findOrFail($request->user_id);
            $predictions = $request->predictions;
            
            $this->riskPredictionService->storePredictions($user, $predictions);
            
            return $this->responseJSON(null, "Predictions stored successfully", 201);
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to store predictions: " . $e->getMessage(), 500);
        }
    }
}