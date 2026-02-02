<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\HabitLog;
use App\Models\MedicalDocument;
use App\Models\User;
use App\Models\BodyMetric;
use Carbon\Carbon;

class RagIngestionService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.vitalens_intelligence.base_url');
    }

    public function ingestHabitLog(HabitLog $log): bool
    {
        try {
            $text = "Date: {$log->created_at->format('Y-m-d')}\n";
            $text .= "User Log: {$log->raw_text}\n";
            
            if ($log->ai_insight) {
                $text .= "AI Insight: {$log->ai_insight}";
            }

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(30)->post("{$this->baseUrl}/rag/ingest", [
                'user_id' => $log->user_id,
                'source_type' => 'habit_log',
                'source_id' => $log->id,
                'text' => $text,
                'date' => $log->created_at->format('Y-m-d'),
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            return false;
        }
    }

    public function ingestDocument(MedicalDocument $doc, string $extractedText): bool
    {
        try {
            $date = $doc->document_date 
                ? $doc->document_date->format('Y-m-d') 
                : $doc->created_at->format('Y-m-d');

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(30)->post("{$this->baseUrl}/rag/ingest", [
                'user_id' => $doc->user_id,
                'source_type' => 'medical_document',
                'source_id' => $doc->id,
                'text' => $extractedText,
                'date' => $date,
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            return false;
        }
    }

    public function ingestBodyMetrics(User $user, array $metrics): bool
    {
        try {
            // Map gender
            $genderMap = [1 => 'Male', 2 => 'Female'];
            $genderRaw = $user->gender;
            $gender = isset($genderMap[$genderRaw]) ? $genderMap[$genderRaw] : ($metrics['gender'] ?? 'Not specified');

            // Calculate age if birth_date exist
            $age = $metrics['age'] ?? 'Not specified';
            if ($user->birth_date) {
                $age = Carbon::parse($user->birth_date)->age;
            }

            $dob = $user->birth_date ? Carbon::parse($user->birth_date)->format('Y-m-d') : 'Not specified';

            $text = "User Profile Data:\n";
            $text .= "Height: {$metrics['height']} cm\n" ;
            $text .= "Weight: {$metrics['weight']} kg\n";
            $text .= "Age: {$age} years\n";
            $text .= "Date of Birth: {$dob}\n";
            $text .= "Gender: {$gender}";

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(30)->post("{$this->baseUrl}/rag/ingest", [
                'user_id' => $user->id,
                'source_type' => 'body_metrics',
                'source_id' => $user->id,
                'text' => $text,
                'date' => now()->format('Y-m-d'),
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            return false;
        }
    }

    public function ingestUserRiskData(User $user): bool
    {
        try {
            // Get all recent predictions
            $allPredictions = $user->riskPredictions()
                ->with('riskType')
                ->latest('created_at')
                ->get();
            
            if ($allPredictions->isEmpty()) {
                return true;
            }

            // Get only the most recent prediction for each risk type
            $latestRisks = $allPredictions->unique('risk_type_id')->values();
            
            $predictionDate = $latestRisks->first()->created_at;
            $dateFormatted = $predictionDate->format('Y-m-d');
            
            $text = "Risk Predictions as of {$dateFormatted}:\n\n";
            
            foreach ($latestRisks as $risk) {
                $percentage = round($risk->probability * 100, 1);
                $text .= "{$risk->riskType->display_name}: {$percentage}% risk\n";
                
                // Include the AI insight if it exists
                if ($risk->ai_insight) {
                    $text .= "Analysis: {$risk->ai_insight}\n";
                }
                $text .= "\n";
            }

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(30)->post("{$this->baseUrl}/rag/ingest", [
                'user_id' => $user->id,
                'source_type' => 'risk_predictions',
                'source_id' => $user->id . '_' . $predictionDate->format('Ymd'),
                'text' => $text,
                'date' => $dateFormatted,
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            return false;
        }
    }

}