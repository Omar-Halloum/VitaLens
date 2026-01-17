<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\HabitLog;
use App\Models\MedicalDocument;

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
}