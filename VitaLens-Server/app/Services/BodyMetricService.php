<?php

namespace App\Services;

use App\Models\User;
use App\Models\BodyMetric;
use App\Models\HealthVariable;

class BodyMetricService
{
    public function addMetrics(User $user, array $metrics)
    {
        // fetch all relevant HealthVariables that match the keys in the $metrics array
        $variables = HealthVariable::whereIn('key', array_keys($metrics))->get()->keyBy('key');

        foreach ($metrics as $key => $value) {

            if (!isset($variables[$key]) || is_null($value)) {
                continue;
            }

            $variable = $variables[$key];

            $metric = new BodyMetric;
            $metric->user_id = $user->id;
            $metric->health_variable_id = $variable->id;
            $metric->unit_id = $variable->unit_id;
            $metric->value = $value;

            $metric->save();
        }
    }
}