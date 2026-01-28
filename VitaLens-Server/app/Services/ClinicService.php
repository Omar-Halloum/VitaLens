<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\User;
use App\Services\MedicalDocumentService;
use App\Services\RiskPredictionService;
use App\Services\EngineeredFeatureService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;

class ClinicService
{
    protected $medicalDocumentService;
    protected $riskPredictionService;
    protected $engineeredFeatureService;

    public function __construct(
        MedicalDocumentService $medicalDocumentService,
        RiskPredictionService $riskPredictionService,
        EngineeredFeatureService $engineeredFeatureService
    ) {
        $this->medicalDocumentService = $medicalDocumentService;
        $this->riskPredictionService = $riskPredictionService;
        $this->engineeredFeatureService = $engineeredFeatureService;
    }

    public function getClinics()
    {
        $clinic = new Clinic();

        return $clinic->select(['id', 'name', 'drive_folder_id'])
            ->whereNotNull('drive_folder_id')
            ->get();
    }

    public function getClinicByFolderId(string $folderId): Clinic
    {
        return Clinic::where('drive_folder_id', $folderId)->firstOrFail();
    }

    public function analyzeDocument(string $patientFolderId, UploadedFile $file)
    {
        $user = User::where('drive_folder_id', $patientFolderId)->first();
        
        if (!$user) {
            throw new \Exception("Patient not found for folder ID: {$patientFolderId}");
        }

        $document = $this->medicalDocumentService->addDocument($user, $file);
        $predictions = $this->riskPredictionService->getUserPredictions($user);
        
        $enrichedPredictions = $predictions->map(function ($prediction) {
            $riskType = $prediction->riskType;
            $factors = $this->riskPredictionService->getRiskFactors($riskType->key ?? '');
            
            return [
                'risk_name' => $riskType->display_name ?? 'Unknown Risk',
                'probability' => $prediction->probability,
                'confidence_level' => $prediction->confidence_level,
                'ai_insight' => $prediction->ai_insight,
                'factors' => $factors
            ];
        })->toArray();
        
        // Generate PDF
        $pdf = Pdf::loadView('reports.risk-report', [
            'patient' => $user,
            'predictions' => $enrichedPredictions,
            'date' => $document->document_date ?? now()->toDateString(),
            'filename' => $file->getClientOriginalName()
        ]);
        
        return $pdf->output(); // Returns raw binary string
    }
}