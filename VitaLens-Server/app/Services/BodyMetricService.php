<?php

namespace App\Services;

use App\Models\User;
use App\Models\BodyMetric;
use App\Models\HealthVariable;

class BodyMetricService
{
    protected $ragIngestionService;

    public function __construct(RagIngestionService $ragIngestionService)
    {
        $this->ragIngestionService = $ragIngestionService;
    }
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

        if (isset($metrics['height']) || isset($metrics['weight'])) {
            $this->ragIngestionService->ingestBodyMetrics($user, [
                'height' => $metrics['height'] ?? 'Not specified',
                'weight' => $metrics['weight'] ?? 'Not specified',
                'age' => $user->age ?? 'Not specified',
                'gender' => $user->gender ?? 'Not specified',
            ]);
            
            $this->ragIngestionService->ingestUserRiskData($user);
        }
    }

    public function getUserMetrics(User $user): array
    {
        $bodyMetrics = $user->bodyMetrics()
            ->with(['healthVariable', 'unit'])
            ->latest('created_at')
            ->take(2)
            ->get();
        
        $formatted = [];
        foreach ($bodyMetrics as $metric) {
            $formatted[$metric->healthVariable->key] = [
                'value' => (float) $metric->value,
                'unit' => $metric->unit->name,
                'updated_at' => $metric->created_at
            ];
        }

        return $formatted;
    }
}