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
    protected function calculateAge(User $user): ?int
    {
        if (!$user->birth_date) {
            return null;
        }
        
        return Carbon::parse($user->birth_date)->age;
    }

    protected function encodeGender(User $user): ?int
    {
        if (!$user->gender) {
            return null;
        }
        
        // Male = 1, Female = 2
        return strtolower($user->gender) === 'male' ? 1 : 2;
    }

    protected function calculateBMI(User $user): ?float
    {
        $height = $this->getLatestBodyMetric($user, 'height');
        $weight = $this->getLatestBodyMetric($user, 'weight');
        
        if (!$height || !$weight || $height <= 0) {
            return null;
        }
        
        // BMI = weight (kg) / height (m)^2
        $heightInMeters = $height / 100;
        
        return round($weight / ($heightInMeters * $heightInMeters), 2);
    }

    protected function getLatestBodyMetric(User $user, string $variableKey): ?float
    {
        $variable = HealthVariable::where('key', $variableKey)->first();
        
        if (!$variable) {
            return null;
        }
        
        $metric = BodyMetric::where('user_id', $user->id)
            ->where('health_variable_id', $variable->id)
            ->orderBy('created_at', 'desc')
            ->first();
        
        return $metric ? (float) $metric->value : null;
    }

}
