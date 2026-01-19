<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'weight' => ['sometimes', 'numeric'],
            'height' => ['sometimes', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Name must be a text string.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'weight.numeric' => 'Weight must be a number.',
            'height.numeric' => 'Height must be a number.',
        ];
    }
}