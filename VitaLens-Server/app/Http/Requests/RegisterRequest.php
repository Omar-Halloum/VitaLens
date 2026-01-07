<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' =>         ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:8'],
            'gender'       => ['required', 'string', 'in:male,female'],
            'birth_date'   => ['required', 'date', 'before:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'      => 'An email address is mandatory for your health profile.',
            'email.unique'        => 'This email already exist',
            'gender.in'           => 'Please select a valid gender (male or female) for accurate risk analysis.',
            'birth_date.before'   => 'Your birth date must be a date in the past.',
        ];
    }
}