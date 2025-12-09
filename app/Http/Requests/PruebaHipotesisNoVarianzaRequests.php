<?php

namespace App\Http\Requests;

class PruebaHipotesisNoVarianzaRequests extends ApiFormRequest
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
            'promedio' => 'required|numeric|min:0',
            'varianza' => 'required|numeric|min:0',
            'u0' => 'required|numeric|min:0',
            'cantidad' => 'required|numeric|min:0',
            'confiabilidad' => 'required|numeric|min:0|max:1',
            'modo' => 'required|integer|min:0|max:2'
        ];
    }

    public function messages(): array
    {
        return [
            'promedio.required' => 'El campo promedio es obligatorio.',
            'promedio.numeric' => 'El campo promedio debe ser un número.',
            'promedio.min' => 'El campo promedio debe ser al menos 0.',
            'varianza.required' => 'El campo varianza es obligatorio.',
            'varianza.numeric' => 'El campo varianza debe ser un número.',
            'varianza.min' => 'El campo varianza debe ser al menos 0.',
            'u0.required' => 'El campo u0 es obligatorio.',
            'u0.numeric' => 'El campo u0 debe ser un número.',
            'u0.min' => 'El campo u0 debe ser al menos 0.',
            'cantidad.required' => 'El campo cantidad es obligatorio.',
            'cantidad.numeric' => 'El campo cantidad debe ser un número.',
            'cantidad.min' => 'El campo cantidad debe ser al menos 0.',
            'modo.required' => 'El campo modo es obligatorio.',
            'modo.numeric' => 'Se debe seleccionar un modo.',
            'modo.min' => 'Modo invalido. Selecciona una opcion de hipotesis valida.',
            'modo.max' => 'Modo invalido. Selecciona una opcion de hipotesis valida.',
        ];
    }

}
