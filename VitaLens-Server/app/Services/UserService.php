<?php

namespace App\Services;

use App\Models\User;
use App\Services\BodyMetricService;
use App\Services\EngineeredFeatureService;
use App\Services\RiskPredictionService;

class UserService
{
    protected $bodyMetricService;
    protected $engineeredFeatureService;
    protected $riskPredictionService;

    public function __construct(
        BodyMetricService $bodyMetricService,
        EngineeredFeatureService $engineeredFeatureService,
        RiskPredictionService $riskPredictionService
    ) {
        $this->bodyMetricService = $bodyMetricService;
        $this->engineeredFeatureService = $engineeredFeatureService;
        $this->riskPredictionService = $riskPredictionService;
    }

    public function getProfile(User $user): User
    {
        $user->load('userType');
        $latestWeight = $user->bodyMetrics()
            ->whereHas('healthVariable', function($q) {
                $q->where('key', 'weight');
            })
            ->orderBy('created_at', 'desc')
            ->first();
            
        $latestHeight = $user->bodyMetrics()
            ->whereHas('healthVariable', function($q) {
                $q->where('key', 'height');
            })
            ->orderBy('created_at', 'desc')
            ->first();
        
        $user->weight = $latestWeight ? $latestWeight->value : null;
        $user->height = $latestHeight ? $latestHeight->value : null;
        
        return $user;
    }

    public function updateProfile(User $user, array $data): void
    {
        if (isset($data['name']) && $data['name'] !== $user->name) {
            $user->update(['name' => $data['name']]);
        }

        $metrics = [];
        if (isset($data['weight'])) {
            $metrics['weight'] = $data['weight'];
        }
        if (isset($data['height'])) {
            $metrics['height'] = $data['height'];
        }

        if (!empty($metrics)) {
            $this->bodyMetricService->addMetrics($user, $metrics);
            $this->engineeredFeatureService->prepareUserFeatures($user);
            $this->riskPredictionService->predictUserRisks($user);
        }
    }

    public function importClinicPatients(array $usersData, int $clinicId): array
    {
        $createdUsers = [];

        foreach ($usersData as $patientData) {
            $user = new User;
            $user->name = $patientData['name'];
            $user->email = $patientData['email'];
            $user->gender = $patientData['gender'];
            $user->birth_date = $patientData['birth_date'];
            $user->drive_folder_link = $patientData['drive_folder_link'];
            $user->drive_folder_id = $patientData['drive_folder_id'] ?? null;
            $user->clinic_id = $clinicId;
            $user->user_type_id = 2;
            $user->password = null;

            $user->save();

            $metrics = [];
            if (isset($patientData['weight'])) {
                $metrics['weight'] = $patientData['weight'];
            }
            if (isset($patientData['height'])) {
                $metrics['height'] = $patientData['height'];
            }

            if (!empty($metrics)) {
                $this->bodyMetricService->addMetrics($user, $metrics);
            }

            $createdUsers[] = $user;
        }

        return $createdUsers;
    }
}