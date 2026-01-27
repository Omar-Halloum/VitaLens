<?php

namespace App\Services;

use App\Models\User;
use App\Models\RiskPrediction;
use App\Models\RiskType;
use App\Models\RiskRequirement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Prompts\RiskInsightPrompts;

class RiskPredictionService
{
    protected $engineeredFeatureService;
    protected $riskTypeMap;
    protected $ragIngestionService;
    protected $aiService;

    public function __construct(
        EngineeredFeatureService $engineeredFeatureService,
        RagIngestionService $ragIngestionService,
        AIService $aiService
    )
    {
        $this->engineeredFeatureService = $engineeredFeatureService;
        $this->ragIngestionService = $ragIngestionService;
        $this->aiService = $aiService;
        // cache risk type IDs (keep existing code)
        $this->riskTypeMap = RiskType::pluck('id', 'key')->toArray();
    }
    
    
    protected function getRiskTypeId(string $key): ?int
    {
        return $this->riskTypeMap[$key] ?? null;
    }

    public function predictUserRisks(User $user, ?array $features = null, ?string $predictionDate = null): array
    {
        if ($features === null) {
            $features = $this->engineeredFeatureService->formatForPrediction($user);
        }
        
        if (empty($features)) {
            return [
                'success' => false,
                'message' => 'No features available for prediction'
            ];
        }
        
        try {
            $baseUrl = config('services.vitalens_intelligence.base_url');
            
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(30)
                ->post("{$baseUrl}/predict/all", [
                    'user_id' => $user->id,
                    'features' => $features
                ]);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'ML service error'
                ];
            }
            
            $data = $response->json();
            
            if (!($data['success'] ?? false)) {
                return [
                    'success' => false,
                    'message' => $data['message'] ?? 'Prediction failed'
                ];
            }
            
            $this->storePredictions($user, $data['predictions'] ?? [], $features, $predictionDate);
            
            return [
                'success' => true,
                'message' => 'Predictions completed successfully',
                'predictions' => $data['predictions'] ?? []
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to connect to ML service'
            ];
        }
    }

    public function storePredictions(User $user, array $predictions, array $features = [], ?string $predictionDate = null): void
    {
        foreach ($predictions as $prediction) {
            $riskKey = $prediction['risk_type'] ?? null;
            $probability = $prediction['probability'] ?? null;
            $confidenceLevel = $prediction['confidence_level'] ?? null;
            
            $riskTypeId = $this->getRiskTypeId($riskKey);
            if (!$riskTypeId) {
                continue;
            }
            
            $riskType = RiskType::find($riskTypeId);
            $insight = $this->generateInsightForRisk($riskType, $probability, $features);
            
            $riskPrediction = new RiskPrediction;
            $riskPrediction->user_id = $user->id;
            $riskPrediction->risk_type_id = $riskTypeId;
            $riskPrediction->probability = $probability;
            $riskPrediction->confidence_level = $confidenceLevel;
            $riskPrediction->ai_insight = $insight;
            
            // Backdate timestamp if date is provided
            if ($predictionDate) {
                $riskPrediction->created_at = $predictionDate;
            }
            
            $riskPrediction->save();
        }
        
        // Ingest the new risk data into RAG
        $this->ragIngestionService->ingestUserRiskData($user);
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

    public function getRiskHistory(User $user, ?string $riskKey = null, ?string $startDate = null, ?string $endDate = null)
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

    protected function generateInsightForRisk(RiskType $riskType, float $probability, array $features): ?string
    {
        try {
            $prompt = RiskInsightPrompts::generateInsightPrompt(
                $riskType->display_name,
                $riskType->key,
                $probability,
                $features
            );
            
            $aiResponse = $this->aiService->aiCall($prompt);
            $decoded = json_decode($aiResponse, true);
            
            return $decoded['insight'] ?? null;
            
        } catch (\Exception $e) {
            return null;
        }
    }
}