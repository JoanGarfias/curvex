<?php

namespace App\Http\Requests;

class PruebaHipotesisNoVarianzaRequests2 extends ApiFormRequest
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
        'promedio1' => 'required|numeric|min:0',
        'cantidad1' => 'required|numeric|min:0',
        'promedio2' => 'required|numeric|min:0',
        'cantidad2' => 'required|numeric|min:0',
        'confiabilidad' => 'required|numeric|min:0|max:1',
        'modo' => 'required|integer|min:3|max:5',
        'boolEsVarianzaUnica' => 'nullable|bool',        
        // --- Lógica Condicional de Varianza ---

        'varianzap' => [
            'nullable', // Permitir que sea nulo o esté ausente.
            'numeric',
            'min:0',
            // OBLIGATORIO si faltan TODOS los demás campos de varianza (varianza1 y varianza2).
            'required_without_all:varianza1,varianza2', 
        ],

        'varianza1' => [
            'nullable', // Permitir que sea nulo o esté ausente.
            'numeric',
            'min:0',
            // OBLIGATORIO si falta 'varianzap'.
            'required_without:varianzap', 
        ],

        'varianza2' => [
            'nullable', // Permitir que sea nulo o esté ausente.
            'numeric',
            'min:0',
            // OBLIGATORIO si falta 'varianzap'.
            'required_without:varianzap', 
        ],
    ];
    }

    public function messages(): array
    {
        return [
            'promedio1.required' => 'El campo promedio1 es obligatorio.',
            'promedio1.numeric' => 'El campo promedio1 debe ser un número.',
            'promedio1.min' => 'El campo promedio1 debe ser al menos 0.',
            'varianza1.required' => 'El campo varianza1 es obligatorio.',
            'varianza1.numeric' => 'El campo varianza1 debe ser un número.',
            'varianza1.min' => 'El campo varianza1 debe ser al menos 0.',
            'cantidad1.required' => 'El campo cantidad1 es obligatorio.',
            'cantidad1.numeric' => 'El campo cantidad1 debe ser un número.',
            'cantidad1.min' => 'El campo cantidad1 debe ser al menos 0.',
            'promedio2.required' => 'El campo promedio2 es obligatorio.',
            'promedio2.numeric' => 'El campo promedio2 debe ser un número.',
            'promedio2.min' => 'El campo promedio2 debe ser al menos 0.',
            'varianza2.required' => 'El campo varianza2 es obligatorio.',
            'varianza2.numeric' => 'El campo varianza2 debe ser un número.',
            'varianza2.min' => 'El campo varianza2 debe ser al menos 0.',
            'cantidad2.required' => 'El campo cantidad2 es obligatorio.',
            'cantidad2.numeric' => 'El campo cantidad2 debe ser un número.',
            'cantidad2.min' => 'El campo cantidad2 debe ser al menos 0.',
            'modo.required' => 'El campo modo es obligatorio.',
            'modo.numeric' => 'Se debe seleccionar un modo.',
            'modo.min' => 'Modo invalido. Selecciona una opcion de hipotesis valida.',
            'modo.max' => 'Modo invalido. Selecciona una opcion de hipotesis valida.',
        ];
    }
}
