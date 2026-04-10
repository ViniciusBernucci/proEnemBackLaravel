<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['sometimes', 'string', 'max:255'],
            'email'      => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
            'full_name'  => ['sometimes', 'string', 'max:255'],
            'phone'      => ['sometimes', 'nullable', 'string', 'max:20'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string'      => 'O nome deve ser um texto.',
            'name.max'         => 'O nome deve ter no máximo 255 caracteres.',
            'email.email'      => 'O e-mail deve ser válido.',
            'email.unique'     => 'Este e-mail já está em uso.',
            'full_name.string' => 'O nome completo deve ser um texto.',
            'phone.string'     => 'O telefone deve ser um texto.',
            'birth_date.date'  => 'A data de nascimento deve ser uma data válida.',
        ];
    }
}
