<?php

namespace App\Services;

use App\Models\DocumentText;
use App\Models\MedicalDocument;

class DocumentTextService
{
    public function addText(MedicalDocument $document, string $extractedText): DocumentText
    {
        $documentText = new DocumentText;
        $documentText->document_id = $document->id;
        $documentText->extracted_text = $extractedText;
        
        $documentText->save();

        return $documentText;
    }

    public function getTextByDocument(MedicalDocument $document)
    {
        return $document->documentTexts()->first();
    }
}