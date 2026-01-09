<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentTextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_id' => ['required', 'exists:medical_documents,id'],
            'extracted_text' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'document_id.required' => 'Document ID is required.',
            'document_id.exists' => 'The specified document does not exist.',
            'extracted_text.required' => 'Extracted text content is required.',
        ];
    }
}