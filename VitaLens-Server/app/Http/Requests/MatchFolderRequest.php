<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MatchFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'folder_id' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'folder_id.required' => 'The Google Drive folder ID is required.',
            'folder_id.string' => 'The folder ID format is invalid.',
        ];
    }
}
