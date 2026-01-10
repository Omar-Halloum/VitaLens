<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePredictionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'predictions' => ['required', 'array', 'min:1'],
            'predictions.*.risk_type' => ['required', 'string', 'exists:risk_types,key'],
            'predictions.*.probability' => ['nullable', 'numeric', 'between:0,1'],
            'predictions.*.confidence_level' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID is required.',
            'user_id.exists' => 'User not found.',
            'predictions.required' => 'Predictions array is required.',
            'predictions.*.risk_type.required' => 'Risk type is required for each prediction.',
            'predictions.*.risk_type.exists' => 'Invalid risk type. Valid options: type_2_diabetes, heart_disease, hypertension, kidney_disease.',
            'predictions.*.probability.between' => 'Probability must be between 0 and 1.',
        ];
    }
}

