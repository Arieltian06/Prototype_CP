<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email', 'max:255'], 'password' => ['required', 'string', 'max:255']];
    }

    public function messages(): array
    {
        return ['email.required' => 'Masukkan email admin.', 'email.email' => 'Format email belum valid.', 'password.required' => 'Masukkan kata sandi.'];
    }
}
