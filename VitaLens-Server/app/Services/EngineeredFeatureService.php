<?php

namespace App\Services;

use App\Models\User;
use App\Models\EngineeredFeature;
use App\Models\FeatureDefinition;
use App\Models\BodyMetric;
use App\Models\MedicalMetric;
use App\Models\HabitMetric;
use App\Models\HealthVariable;
use Carbon\Carbon;

class EngineeredFeatureService
{
    protected $variableMap;

    public function __construct()
    {
        $this->variableMap = HealthVariable::pluck('id', 'key')->toArray();
    }

    public function prepareUserFeatures(User $user): array
    {
        $height = $this->getLatestBodyMetric($user, 'height');
        $weight = $this->getLatestBodyMetric($user, 'weight');
        
        $features = [
            // Demographics
            'age' => $this->calculateAge($user),
            'gender' => $this->encodeGender($user),
            
            // Body metrics
            'height' => $height,
            'weight' => $weight,
            'bmi' => $this->calculateBMIFromValues($height, $weight),
            
            // Medical metrics (latest values)
            'fasting_glucose' => $this->getLatestMedicalMetric($user, 'fasting_glucose'),
            'hba1c' => $this->getLatestMedicalMetric($user, 'hba1c'),
            'systolic_bp' => $this->getLatestMedicalMetric($user, 'systolic_bp'),
            'diastolic_bp' => $this->getLatestMedicalMetric($user, 'diastolic_bp'),
            'ldl_cholesterol' => $this->getLatestMedicalMetric($user, 'ldl_cholesterol'),
            'hdl_cholesterol' => $this->getLatestMedicalMetric($user, 'hdl_cholesterol'),
            'triglycerides' => $this->getLatestMedicalMetric($user, 'triglycerides'),
            'creatinine' => $this->getLatestMedicalMetric($user, 'creatinine'),
            'bun' => $this->getLatestMedicalMetric($user, 'bun'),
            'uric_acid' => $this->getLatestMedicalMetric($user, 'uric_acid'),
            'waist_circumference' => $this->getLatestMedicalMetric($user, 'waist_circumference'),
            
            // Habit metrics (averages for daily habits)
            'sleep_duration' => $this->getAverageHabitMetric($user, 'sleep_duration', 30),
            'activity_moderate' => $this->getAverageHabitMetric($user, 'activity_moderate', 30),
            'activity_vigorous' => $this->getAverageHabitMetric($user, 'activity_vigorous', 30),
            'alcohol_intake' => $this->getAverageHabitMetric($user, 'alcohol_intake', 30),
            
            'smoking_status' => $this->getLatestHabitMetric($user, 'smoking_status'),
        ];
        
        $this->storeFeatures($user, $features);
        
        return $features;
    }

    protected function calculateAge(User $user): ?int
    {
        return $user->birth_date ? Carbon::parse($user->birth_date)->age : null;
    }

    protected function encodeGender(User $user): ?int
    {
        if (!$user->gender) {
            return null;
        }
        
        return strtolower($user->gender) === 'male' ? 1 : 2;
    }

    protected function calculateBMIFromValues(?float $height, ?float $weight): ?float
    {
        if (!$height || !$weight || $height <= 0) {
            return null;
        }
        
        // BMI = weight (kg) / height (m)^2
        $heightInMeters = $height / 100;
        
        return round($weight / ($heightInMeters * $heightInMeters), 2);
    }

    protected function getVariableId(string $key): ?int
    {
        return $this->variableMap[$key] ?? null;
    }

    protected function getLatestBodyMetric(User $user, string $variableKey): ?float
    {
        $variableId = $this->getVariableId($variableKey);
        if (!$variableId) {
            return null;
        }
        
        $metric = BodyMetric::where('user_id', $user->id)
            ->where('health_variable_id', $variableId)
            ->latest('created_at')
            ->first();
        
        return $metric ? (float) $metric->value : null;
    }

    protected function getLatestMedicalMetric(User $user, string $variableKey): ?float
    {
        $variableId = $this->getVariableId($variableKey);
        if (!$variableId) {
            return null;
        }
        
        $metric = MedicalMetric::where('user_id', $user->id)
            ->where('health_variable_id', $variableId)
            ->latest('measured_at')
            ->first();
        
        return $metric ? (float) $metric->value : null;
    }

    protected function getAverageHabitMetric(User $user, string $variableKey, int $days = 30): ?float
    {
        $variableId = $this->getVariableId($variableKey);
        if (!$variableId) {
            return null;
        }
        
        $startDate = Carbon::now()->subDays($days);
        
        $average = HabitMetric::where('user_id', $user->id)
            ->where('health_variable_id', $variableId)
            ->where('created_at', '>=', $startDate)
            ->avg('value');
        
        return $average ? round((float) $average, 2) : null;
    }

    protected function getLatestHabitMetric(User $user, string $variableKey): ?float
    {
        $variableId = $this->getVariableId($variableKey);
        if (!$variableId) {
            return null;
        }
        
        $metric = HabitMetric::where('user_id', $user->id)
            ->where('health_variable_id', $variableId)
            ->latest('created_at')
            ->first();
        
        return $metric ? (float) $metric->value : null;
    }

    public function storeFeatures(User $user, array $features): void
    {
        // static cache to avoid re-fetching on multiple calls in same request
        static $featureDefinitions;
        if (!$featureDefinitions) {
            $featureDefinitions = FeatureDefinition::all()->keyBy('feature_name');
        }
        
        foreach ($features as $featureName => $value) {
            if (is_null($value) || !isset($featureDefinitions[$featureName])) {
                continue;
            }
            
            $definition = $featureDefinitions[$featureName];
            
            $engineeredFeature = new EngineeredFeature;
            $engineeredFeature->user_id = $user->id;
            $engineeredFeature->feature_definition_id = $definition->id;
            $engineeredFeature->feature_value = $value;
            $engineeredFeature->save();
        }
    }

    public function getUserFeatures(User $user)
    {
        $allFeatures = EngineeredFeature::where('user_id', $user->id)
            ->with('featureDefinition')
            ->latest('created_at')
            ->get();
        
        return $allFeatures->unique('feature_definition_id')->values();
    }

    public function getFeatureHistory(
        User $user, 
        ?string $featureName = null,
        ?string $startDate = null,
        ?string $endDate = null
    )
    {
        $query = EngineeredFeature::where('user_id', $user->id)
            ->with('featureDefinition')
            ->latest('created_at');
        
        // Date filtering default to last 4 months
        $query->where('created_at', '>=', $startDate ?? Carbon::now()->subMonths(4));
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        if ($featureName) {
            $query->whereHas('featureDefinition', function($q) use ($featureName) {
                $q->where('feature_name', $featureName);
            });
        }
        
        return $query->get();
    }

    public function formatForPrediction(User $user): array
    {
        $latestFeatures = $this->getUserFeatures($user);
        
        $formatted = [];
        foreach ($latestFeatures as $feature) {
            $featureName = $feature->featureDefinition->feature_name;
            $formatted[$featureName] = (float) $feature->feature_value;
        }
        
        return $formatted;
    }
}