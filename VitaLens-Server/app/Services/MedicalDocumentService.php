<?php

namespace App\Services;

use App\Models\User;
use App\Models\MedicalDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Services\HealthDataExtractionService;
use App\Services\EngineeredFeatureService;
use App\Services\RiskPredictionService;

class MedicalDocumentService
{
    protected $documentTextService;
    protected $ragIngestionService;
    protected $healthDataExtractionService;
    protected $engineeredFeatureService;
    protected $riskPredictionService;

    public function __construct(
        DocumentTextService $documentTextService,
        RagIngestionService $ragIngestionService,
        HealthDataExtractionService $healthDataExtractionService,
        EngineeredFeatureService $engineeredFeatureService,
        RiskPredictionService $riskPredictionService
    ) {
        $this->documentTextService = $documentTextService;
        $this->ragIngestionService = $ragIngestionService;
        $this->healthDataExtractionService = $healthDataExtractionService;
        $this->engineeredFeatureService = $engineeredFeatureService;
        $this->riskPredictionService = $riskPredictionService;
    }

    public function addDocument(User $user, UploadedFile $file): MedicalDocument
    {
        $filename = $this->generateFilename($user, $file);
        
        $filePath = $file->storeAs(
            "medical_documents/{$user->id}", 
            $filename, 
            'public' 
        );
        
        $document = new MedicalDocument;
        $document->user_id = $user->id;
        $document->file_path = $filePath;
        $document->file_type = $this->getFileExtension($file);
        $document->document_date = null;
        
        $document->save();

        $this->processOcr($document);

        return $document;
    }

    public function processOcr(MedicalDocument $document)
    {
        $absolutePath = Storage::disk('public')->path($document->file_path);

        if (!file_exists($absolutePath)) {
            throw new \Exception("OCR Error: File not found at $absolutePath");
        }

        $fileStream = fopen($absolutePath, 'r');

        try {
            $baseUrl = config('services.vitalens_intelligence.base_url');
            $intelligenceUrl = $baseUrl . '/ocr/extract';

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(60)
                ->attach('file', $fileStream, basename($document->file_path))
                ->post($intelligenceUrl, [
                    'file_type' => $document->file_type
                ]);

            if ($response->failed()) {
                throw new \Exception('OCR API Error: ' . $response->body());
            }

            $result = $response->json();
            $text = $result['extracted_text'] ?? null;

            if ($text) {
                $this->documentTextService->addText($document, $text);
                
                $this->ragIngestionService->ingestDocument($document, $text);
                
                $this->healthDataExtractionService->extractFromDocument($document);
                
                $user = $document->user;
                $features = $this->engineeredFeatureService->prepareUserFeatures($user);
                $this->riskPredictionService->predictUserRisks($user, $features);
            }

        } catch (\Exception $e) {
            throw new \Exception('Intelligence Service Error: ' . $e->getMessage());
        } finally {
            if (is_resource($fileStream)) {
                fclose($fileStream);
            }
        }
    }

    public function getUserDocuments(User $user)
    {
        return $user->medicalDocuments()->orderBy('created_at', 'desc')->get();
    }

    protected function getFileExtension(UploadedFile $file): string
    {
        return strtoupper($file->getClientOriginalExtension());
    }

    protected function generateFilename(User $user, UploadedFile $file): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
        
        $sanitizedUserName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $user->name);
        
        $timestamp = now()->format('YmdHis');
        
        return substr("{$sanitizedUserName}_{$sanitizedName}_{$timestamp}", 0, 150) . '.' . $file->getClientOriginalExtension();
    }

    public function updateDocumentDate(MedicalDocument $document, $documentDate): void
    {
        $document->update(['document_date' => $documentDate]);
    }
}