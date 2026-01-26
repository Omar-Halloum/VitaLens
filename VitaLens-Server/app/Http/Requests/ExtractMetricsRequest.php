<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExtractMetricsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_id' => ['required', 'integer', 'exists:medical_documents,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'document_id.required' => 'Document ID is required to extract metrics.',
            'document_id.exists' => 'The specified document does not exist.',
        ];
    }
}