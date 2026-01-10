<?php

namespace App\Services;

use App\Models\User;
use App\Models\HabitMetric;
use App\Models\HabitLog;
use App\Models\HealthVariable;

class HabitMetricService
{
    public function storeMetrics(int $userId, int $habitLogId, array $metrics)
    {
        
        $variables = HealthVariable::all()->keyBy('key');

        foreach ($metrics as $metric) {
            $key = $metric['key'] ?? null;
            $value = $metric['value'] ?? null;

            if (!$key || is_null($value)) {
                continue;
            }

            if (!isset($variables[$key])) {
                continue;
            }

            $variable = $variables[$key];

            $habitMetric = new HabitMetric;
            $habitMetric->user_id = $userId;
            $habitMetric->habit_log_id = $habitLogId;
            $habitMetric->health_variable_id = $variable->id;
            $habitMetric->unit_id = $variable->unit_id;
            $habitMetric->value = $value;

            $habitMetric->save();
        }
    }

    public function getMetricsByUser(User $user)
    {
        return HabitMetric::where('user_id', $user->id)
            ->with(['healthVariable', 'unit', 'habitLog'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getMetricsByLog(int $habitLogId)
    {
        return HabitMetric::where('habit_log_id', $habitLogId)
            ->with(['healthVariable', 'unit'])
            ->get();
    }
}

