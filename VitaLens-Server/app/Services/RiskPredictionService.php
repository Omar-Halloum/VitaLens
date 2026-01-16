<?php

namespace App\Services;

use App\Models\User;
use App\Models\RiskPrediction;
use App\Models\RiskType;
use App\Models\RiskRequirement;
use Carbon\Carbon;

class RiskPredictionService
{
    protected $engineeredFeatureService;
    protected $riskTypeMap;

    public function __construct(EngineeredFeatureService $engineeredFeatureService)
    {
        $this->engineeredFeatureService = $engineeredFeatureService;
        // cache risk type IDs
        $this->riskTypeMap = RiskType::pluck('id', 'key')->toArray();
    }
    
    protected function getRiskTypeId(string $key): ?int
    {
        return $this->riskTypeMap[$key] ?? null;
    }

    public function predictUserRisks(User $user): array
    {
        // Get features formatted for prediction
        $features = $this->engineeredFeatureService->formatForPrediction($user);
        
        if (empty($features)) {
            return [
                'success' => false,
                'message' => 'No features available for prediction'
            ];
        }
        
        // TODO: Call Python ML service here

        return [
            'success' => true,
            'message' => 'Features ready for prediction',
            'features' => $features,
            'predictions' => [] // Will be populated by Python
        ];
    }

    public function storePredictions(User $user, array $predictions): void
    {
        foreach ($predictions as $prediction) {
            $riskKey = $prediction['risk_type'] ?? null;
            $probability = $prediction['probability'] ?? null;
            $confidenceLevel = $prediction['confidence_level'] ?? null;
            
            $riskTypeId = $this->getRiskTypeId($riskKey);
            if (!$riskTypeId) {
                continue;
            }
            
            $riskPrediction = new RiskPrediction;
            $riskPrediction->user_id = $user->id;
            $riskPrediction->risk_type_id = $riskTypeId;
            $riskPrediction->probability = $probability;
            $riskPrediction->confidence_level = $confidenceLevel;
            $riskPrediction->save();
        }
    }

    public function getUserPredictions(User $user)
    {
        $allPredictions = RiskPrediction::where('user_id', $user->id)
            ->with('riskType')
            ->latest('created_at')
            ->get();
        
        // Get unique predictions by risk type
        return $allPredictions->unique('risk_type_id')->values();
    }

    public function getRiskPrediction(User $user, string $riskKey)
    {
        $riskTypeId = $this->getRiskTypeId($riskKey);
        if (!$riskTypeId) {
            return null;
        }
        
        return RiskPrediction::where('user_id', $user->id)
            ->where('risk_type_id', $riskTypeId)
            ->with('riskType')
            ->latest('created_at')
            ->first();
    }

    public function getRiskHistory(
        User $user, 
        ?string $riskKey = null,
        ?string $startDate = null,
        ?string $endDate = null
    )
    {
        $query = RiskPrediction::where('user_id', $user->id)
            ->with('riskType')
            ->latest('created_at');
        
        // Date filtering: default to last 4 months
        $query->where('created_at', '>=', $startDate ?? Carbon::now()->subMonths(4));
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        // Filter by specific risk type using cached ID
        if ($riskKey) {
            $riskTypeId = $this->getRiskTypeId($riskKey);
            if ($riskTypeId) {
                $query->where('risk_type_id', $riskTypeId);
            }
        }
        
        return $query->get();
    }

    public function checkDataSufficiency(User $user): array
    {
        $features = $this->engineeredFeatureService->formatForPrediction($user);
        
        $riskTypes = RiskType::with(['riskRequirements.featureDefinition'])->get();
        
        $sufficiency = [];
        
        foreach ($riskTypes as $riskType) {
            $requiredFeatures = [];
            $optionalFeatures = [];
            $missingRequired = [];
            $missingOptional = [];
            
            foreach ($riskType->riskRequirements as $requirement) {
                $featureName = $requirement->featureDefinition->feature_name;
                
                if ($requirement->is_required) {
                    $requiredFeatures[] = $featureName;
                    if (!isset($features[$featureName])) {
                        $missingRequired[] = $featureName;
                    }
                } else {
                    $optionalFeatures[] = $featureName;
                    if (!isset($features[$featureName])) {
                        $missingOptional[] = $featureName;
                    }
                }
            }
            
            $canPredict = empty($missingRequired);
            
            $sufficiency[] = [
                'risk_type' => $riskType->key,
                'display_name' => $riskType->display_name,
                'can_predict' => $canPredict,
                'required_features' => $requiredFeatures,
                'optional_features' => $optionalFeatures,
                'missing_required' => $missingRequired,
                'missing_optional' => $missingOptional
            ];
        }
        
        return $sufficiency;
    }

    public function getRiskFactors(string $riskKey): array
    {
        $riskTypeId = $this->getRiskTypeId($riskKey);
        if (!$riskTypeId) {
            return [];
        }
        
        $requirements = RiskRequirement::where('risk_type_id', $riskTypeId)
            ->with(['featureDefinition' => function($query) {
                $query->select('id', 'feature_name', 'display_name');
            }])
            ->get();
        
        $factors = [];
        
        foreach ($requirements as $requirement) {
            $factors[] = [
                'feature_name' => $requirement->featureDefinition->feature_name,
                'display_name' => $requirement->featureDefinition->display_name,
                'is_required' => (bool) $requirement->is_required,
            ];
        }
        
        return $factors;
    }

    public function getUserTopRisk(User $user): ?array
    {
        $topRisk = RiskPrediction::where('user_id', $user->id)
            ->with('riskType')
            ->orderBy('probability', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if (!$topRisk) {
            return null;
        }
        
        return [
            'risk_key' => $topRisk->riskType->key,
            'display_name' => $topRisk->riskType->display_name,
            'probability' => $topRisk->probability,
            'confidence_level' => $topRisk->confidence_level,
        ];
    }
}