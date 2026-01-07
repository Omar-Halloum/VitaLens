<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_type_id' => ['required', 'integer', 'exists:user_types,id'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'string', 'confirmed', 'min:8'],
            'gender'       => ['required', 'string', 'in:male,female'],
            'birth_date'   => ['required', 'date', 'before:today'],
        ];
    }

    /**
     * Custom error messages for the defined rules.
     */
    public function messages(): array
    {
        return [
            'email.required'      => 'An email address is mandatory for your health profile.',
            'email.unique'        => 'This email already exist',
            'password.confirmed'  => 'The password confirmation does not match.',
            'gender.in'           => 'Please select a valid gender (male or female) for accurate risk analysis.',
            'birth_date.before'   => 'Your birth date must be a date in the past.',
            'user_type_id.exists' => 'The selected account type is invalid.',
        ];
    }
}