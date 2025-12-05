<?php

namespace App\Http\Requests;

class MuestroAceptacionInputNC extends ApiFormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'n' => 'required|integer|min:2',
            'c' => 'required|integer|min:0|max:7',
            '1-alpha' => 'required|numeric|min:0|max:1',
            'beta' => 'required|numeric|min:0|max:1',
        ];
    }

    public function messages(): array
    {
        return [
            'n.required' => 'El tamaño de la muestra (n) es obligatorio.',
            'n.integer' => 'El tamaño de la muestra (n) debe ser un número entero.',
            'n.min' => 'El tamaño de la muestra (n) debe ser al menos 2.',

            'c.required' => 'El número de aceptaciones (c) es obligatorio.',
            'c.integer' => 'El número de aceptaciones (c) debe ser un número entero.',
            'c.min' => 'El número de aceptaciones (c) no puede ser negativo.',
            'c.max' => 'El número de aceptaciones (c) no puede ser mayor que 7.',

            '1-alpha.required' => 'El valor de 1-α es obligatorio.',
            '1-alpha.numeric' => 'El valor de 1-α debe ser un número.',
            '1-alpha.min' => 'El valor de 1-α debe ser al menos 0.',
            '1-alpha.max' => 'El valor de 1-α no puede ser mayor que 1.',

            'beta.required' => 'El valor de β es obligatorio.',
            'beta.numeric' => 'El valor de β debe ser un número.',
            'beta.min' => 'El valor de β debe ser al menos 0.',
            'beta.max' => 'El valor de β no puede ser mayor que 1.',
        ];
    }
}
