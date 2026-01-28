<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'The specified patient could not be found.',
            'document.required' => 'Please upload a medical document.',
            'document.mimes' => 'The document must be a PDF, JPEG, JPG, or PNG file.',
        ];
    }
}
