<?php

namespace App\Services;

use App\Models\User;
use App\Models\MedicalMetric;
use App\Models\MedicalDocument;
use App\Models\HealthVariable;

class MedicalMetricService
{
    public function storeMetrics(int $userId, int $documentId, array $metrics, ?string $measuredAt)
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

            $medicalMetric = new MedicalMetric;
            $medicalMetric->user_id = $userId;
            $medicalMetric->source_document_id = $documentId;
            $medicalMetric->health_variable_id = $variable->id;
            $medicalMetric->unit_id = $variable->unit_id;
            $medicalMetric->value = $value;
            $medicalMetric->measured_at = $measuredAt;

            $medicalMetric->save();
        }
    }

    public function getMetricsByUser(User $user)
    {
        return MedicalMetric::where('user_id', $user->id)
            ->with(['healthVariable', 'unit', 'document'])
            ->orderBy('measured_at', 'desc')
            ->get();
    }

    public function getMetricsByDocument(int $documentId)
    {
        return MedicalMetric::where('source_document_id', $documentId)
            ->with(['healthVariable', 'unit'])
            ->get();
    }
}