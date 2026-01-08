<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalDocumentRequest;
use App\Services\MedicalDocumentService;
use Illuminate\Http\Request;

class MedicalDocumentController extends Controller
{
    protected $medicalDocumentService;

    public function __construct(MedicalDocumentService $medicalDocumentService)
    {
        $this->medicalDocumentService = $medicalDocumentService;
    }

    public function addDocument(StoreMedicalDocumentRequest $request)
    {
        try {
            $user = $request->user();
            $file = $request->file('document');
            
            $document = $this->medicalDocumentService->addDocument($user, $file);
            
            return $this->responseJSON($document, "Document uploaded successfully", 201);
        } catch (\Exception $e) {
            return $this->responseJSON($e->getMessage(), "failure", 500);
        }
    }

    public function getUserDocuments(Request $request)
    {
        try {
            $user = $request->user();
            $documents = $this->medicalDocumentService->getUserDocuments($user);
            
            return $this->responseJSON($documents, "Documents retrieved successfully");
        } catch (\Exception $e) {
            return $this->responseJSON($e->getMessage(), "failure", 500);
        }
    }
}