<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => ['required', 'file', 'mimes:pdf,jpeg,png,jpg', 'max:10240'],
            'patient_folder_id' => ['required', 'string'],
        ];
    }
}