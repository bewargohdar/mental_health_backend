<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'role' => ['nullable', 'in:user,doctor'],
        ];

        // Additional validation for doctor registration
        if ($this->role === 'doctor' || $this->is('*/register/doctor')) {
            $rules['specialization'] = ['required', 'string', 'max:255'];
            $rules['license_number'] = ['required', 'string', 'max:100', 'unique:doctor_profiles'];
            $rules['bio'] = ['nullable', 'string', 'max:2000'];
            $rules['experience_years'] = ['nullable', 'integer', 'min:0', 'max:70'];
        }

        return $rules;
    }
}
