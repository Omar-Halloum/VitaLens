<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'document' is the key expected in the form-data
            'document' => ['required', 'file', 'mimes:pdf,jpeg,png,jpg', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'Please upload a medical document.',
            'document.mimes' => 'The document must be a PDF or an image (jpeg, png, jpg).',
            'document.max' => 'The document size must not exceed 10MB.',
        ];
    }
}