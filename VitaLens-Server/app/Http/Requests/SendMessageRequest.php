<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Please enter a message',
            'message.max' => 'Message cannot exceed 1000 characters',
        ];
    }
}