<?php

namespace App\Http\Requests\Vet;

use Illuminate\Foundation\Http\FormRequest;

class MedicalServiceRequest extends FormRequest
{
    public function authorize()
    {
        // La autorización se maneja en el controlador
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
        ];
    }
}
