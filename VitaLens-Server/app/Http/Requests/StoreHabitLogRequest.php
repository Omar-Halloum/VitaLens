<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHabitLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'habit_text' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'habit_text.required' => 'Please provide your habit log text.',
            'habit_text.string' => 'The habit text must be a valid string.',
        ];
    }
}

