<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s"\'-]+$/u'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'El nombre solo puede incluir letras, espacios, comillas dobles, apóstrofes y guiones.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'profile_photo.image' => 'La imagen de perfil debe ser un archivo de imagen válido.',
            'profile_photo.mimes' => 'La imagen de perfil debe estar en formato JPG, PNG o WEBP.',
            'profile_photo.max' => 'La imagen de perfil no debe superar los 10 MB.',
        ];
    }

    /**
     * Get custom validation attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'profile_photo' => 'imagen de perfil',
        ];
    }
}
