<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'O arquivo de avatar é obrigatório.',
            'avatar.image'    => 'O arquivo deve ser uma imagem.',
            'avatar.mimes'    => 'O avatar deve ser do tipo: jpg, jpeg, png ou webp.',
            'avatar.max'      => 'O avatar deve ter no máximo 2MB.',
        ];
    }
}
