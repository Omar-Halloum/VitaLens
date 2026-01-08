<?php

namespace App\Services;

use App\Models\User;
use App\Models\MedicalDocument;
use Illuminate\Http\UploadedFile;

class MedicalDocumentService
{
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

        return $document;
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