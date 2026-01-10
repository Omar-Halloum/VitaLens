<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBodyMetricsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'weight' => ['nullable', 'numeric', 'min:1', 'max:400'],
            'height' => ['nullable', 'numeric', 'min:50', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'weight.numeric' => 'Weight must be a number.',
            'weight.min' => 'Weight must be at least 1 kg.',
            'weight.max' => 'Weight cannot exceed 400 kg.',
            'height.numeric' => 'Height must be a number.',
            'height.min' => 'Height must be at least 50 cm.',
            'height.max' => 'Height cannot exceed 300 cm.',
        ];
    }
}