<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClinicPatientsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'folder_id' => ['required', 'string'],
            'users' => ['required', 'array', 'min:1'],
            'users.*.name' => ['required', 'string'],
            'users.*.email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'users.*.gender' => ['required', 'in:1,2'],
            'users.*.drive_folder_link' => ['required', 'url'],
            'users.*.drive_folder_id' => ['nullable', 'string'],
            'users.*.birth_date' => ['required', 'date', 'before:today'],
            'users.*.weight' => ['nullable', 'numeric'],
            'users.*.height' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'folder_id.required' => 'Clinic folder ID is required.',
            'users.min' => 'At least one patient is required.',
            'users.*.email.distinct' => 'Duplicate emails found in the list.',
            'users.*.gender.in' => 'Gender must be 1 (Male) or 2 (Female).',
            'users.*.name' => 'patient name',
            'users.*.email' => 'patient email',
            'users.*.drive_folder_link' => 'drive folder link',
            'users.*.birth_date' => 'birth date',
        ];
    }
}
