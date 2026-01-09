<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentTextRequest;
use App\Services\DocumentTextService;
use App\Models\MedicalDocument;
use Illuminate\Http\Request;

class DocumentTextController extends Controller
{
    protected $documentTextService;

    public function __construct(DocumentTextService $documentTextService)
    {
        $this->documentTextService = $documentTextService;
    }

    public function addText(StoreDocumentTextRequest $request)
    {
        try {
            $document = MedicalDocument::findOrFail($request->document_id);
            $documentText = $this->documentTextService->addText($document, $request->extracted_text);
            
            return $this->responseJSON($documentText, "Document text added successfully", 201);
        } catch (\Exception $e) {
            return $this->responseJSON($e->getMessage(), "failure", 500);
        }
    }

    public function getText(Request $request, $documentId)
    {
        try {
            $document = MedicalDocument::findOrFail($documentId);
            $documentText = $this->documentTextService->getTextByDocument($document);
            
            return $this->responseJSON($documentText, "Document text retrieved successfully");
        } catch (\Exception $e) {
            return $this->responseJSON($e->getMessage(), "failure", 500);
        }
    }
}