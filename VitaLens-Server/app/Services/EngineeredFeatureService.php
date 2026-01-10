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

}
