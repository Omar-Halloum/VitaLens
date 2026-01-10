<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\RiskType;

class GetRiskPredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'riskKey' => ['required', 'string', 'exists:risk_types,key'],
        ];
    }

    protected function prepareForValidation()
    {
        // Merge route parameter into request data for validation
        $this->merge([
            'riskKey' => $this->route('riskKey'),
        ]);
    }

    public function messages(): array
    {
        return [
            'riskKey.required' => 'Risk type key is required.',
            'riskKey.exists' => 'Invalid risk type.',
        ];
    }
}