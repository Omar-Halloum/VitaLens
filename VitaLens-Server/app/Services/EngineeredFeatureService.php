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

    public function prepareUserFeatures(User $user, ?string $asOfDate = null): array
    {
        $height = $this->getLatestBodyMetric($user, 'height', $asOfDate);
        $weight = $this->getLatestBodyMetric($user, 'weight', $asOfDate);
        
        $features = [
            // Demographics
            'age' => $this->calculateAge($user, $asOfDate),
            'gender' => $this->encodeGender($user),
            
            // Body metrics
            'height' => $height,
            'weight' => $weight,
            'bmi' => $this->calculateBMIFromValues($height, $weight),
            
            // Medical metrics (latest values as of specified date)
            'fasting_glucose' => $this->getLatestMedicalMetric($user, 'fasting_glucose', $asOfDate),
            'hba1c' => $this->getLatestMedicalMetric($user, 'hba1c', $asOfDate),
            'systolic_bp' => $this->getLatestMedicalMetric($user, 'systolic_bp', $asOfDate),
            'diastolic_bp' => $this->getLatestMedicalMetric($user, 'diastolic_bp', $asOfDate),
            'ldl_cholesterol' => $this->getLatestMedicalMetric($user, 'ldl_cholesterol', $asOfDate),
            'hdl_cholesterol' => $this->getLatestMedicalMetric($user, 'hdl_cholesterol', $asOfDate),
            'triglycerides' => $this->getLatestMedicalMetric($user, 'triglycerides', $asOfDate),
            'creatinine' => $this->getLatestMedicalMetric($user, 'creatinine', $asOfDate),
            'bun' => $this->getLatestMedicalMetric($user, 'bun', $asOfDate),
            'uric_acid' => $this->getLatestMedicalMetric($user, 'uric_acid', $asOfDate),
            'waist_circumference' => $this->getLatestMedicalMetric($user, 'waist_circumference', $asOfDate),
            
            // Habit metrics (averages for daily habits ending at specified date)
            'sleep_duration' => $this->getAverageHabitMetric($user, 'sleep_duration', 30, $asOfDate),
            'activity_moderate' => $this->getAverageHabitMetric($user, 'activity_moderate', 30, $asOfDate),
            'activity_vigorous' => $this->getAverageHabitMetric($user, 'activity_vigorous', 30, $asOfDate),
            'alcohol_intake' => $this->getAverageHabitMetric($user, 'alcohol_intake', 30, $asOfDate),
            
            'smoking_status' => $this->getLatestHabitMetric($user, 'smoking_status', $asOfDate),
        ];
        
        $this->storeFeatures($user, $features, $asOfDate);
        
        return $features;
    }

    protected function calculateAge(User $user, ?string $asOfDate = null): ?int
    {
        if (!$user->birth_date) {
            return null;
        }
        
        $referenceDate = $asOfDate ? Carbon::parse($asOfDate) : Carbon::now();

        return Carbon::parse($user->birth_date)->diffInYears($referenceDate);
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
        
        $heightInMeters = $height / 100;
        
        return round($weight / ($heightInMeters * $heightInMeters), 2);
    }

    protected function getVariableId(string $key): ?int
    {
        return $this->variableMap[$key] ?? null;
    }

    protected function getLatestBodyMetric(User $user, string $variableKey, ?string $asOfDate = null): ?float
    {
        $variableId = $this->getVariableId($variableKey);
        if (!$variableId) {
            return null;
        }
        
        $query = BodyMetric::where('user_id', $user->id)
            ->where('health_variable_id', $variableId);
        
        if ($asOfDate) {
            $query->where('created_at', '<=', $asOfDate);
        }
        
        $metric = $query->latest('created_at')->first();
        
        if (!$metric && $asOfDate) {
            $metric = BodyMetric::where('user_id', $user->id)
                ->where('health_variable_id', $variableId)
                ->latest('created_at')
                ->first();
        }
        
        return $metric ? (float) $metric->value : null;
    }

    protected function getLatestMedicalMetric(User $user, string $variableKey, ?string $asOfDate = null): ?float
    {
        $variableId = $this->getVariableId($variableKey);
        if (!$variableId) {
            return null;
        }
        
        $query = MedicalMetric::where('user_id', $user->id)
            ->where('health_variable_id', $variableId);
        
        if ($asOfDate) {
            $query->where('measured_at', '<=', $asOfDate);
        }
        
        $metric = $query->latest('measured_at')->first();
        
        return $metric ? (float) $metric->value : null;
    }

    protected function getAverageHabitMetric(User $user, string $variableKey, int $days = 30, ?string $asOfDate = null): ?float
    {
        $variableId = $this->getVariableId($variableKey);
        if (!$variableId) {
            return null;
        }
        
        $endDate = $asOfDate ? Carbon::parse($asOfDate) : Carbon::now();
        $startDate = $endDate->copy()->subDays($days);
        
        $average = HabitMetric::where('user_id', $user->id)
            ->where('health_variable_id', $variableId)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->avg('value');
        
        return $average ? round((float) $average, 2) : null;
    }

    protected function getLatestHabitMetric(User $user, string $variableKey, ?string $asOfDate = null): ?float
    {
        $variableId = $this->getVariableId($variableKey);
        if (!$variableId) {
            return null;
        }
        
        $query = HabitMetric::where('user_id', $user->id)
            ->where('health_variable_id', $variableId);
        
        if ($asOfDate) {
            $query->where('created_at', '<=', $asOfDate);
        }
        
        $metric = $query->latest('created_at')->first();
        
        return $metric ? (float) $metric->value : null;
    }

    public function storeFeatures(User $user, array $features, ?string $asOfDate = null): void
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
            
            // Backdate timestamp if date is provided
            if ($asOfDate) {
                $engineeredFeature->created_at = $asOfDate;
            }
            
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