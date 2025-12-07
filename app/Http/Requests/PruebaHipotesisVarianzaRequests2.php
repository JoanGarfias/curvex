<?php

namespace App\Http\Requests;

class PruebaHipotesisVarianzaRequests2 extends ApiFormRequest
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
            'desviacion1' => 'required|numeric|min:0',
            'cantidad1' => 'required|numeric|min:0',
            'promedio2' => 'required|numeric|min:0',
            'desviacion2' => 'required|numeric|min:0',
            'cantidad2' => 'required|numeric|min:0',
            'confiabilidad' => 'required|numeric|min:0|max:1',
            'modo' => 'required|integer|min:3|max:5'
        ];
    }

    public function messages(): array
    {
        return [
            'promedio1.required' => 'El campo promedio1 es obligatorio.',
            'promedio1.numeric' => 'El campo promedio1 debe ser un número.',
            'promedio1.min' => 'El campo promedio1 debe ser al menos 0.',
            'desviacion1.required' => 'El campo desviacion1 es obligatorio.',
            'desviacion1.numeric' => 'El campo desviacion1 debe ser un número.',
            'desviacion1.min' => 'El campo desviacion1 debe ser al menos 0.',
            'cantidad1.required' => 'El campo cantidad1 es obligatorio.',
            'cantidad1.numeric' => 'El campo cantidad1 debe ser un número.',
            'cantidad1.min' => 'El campo cantidad1 debe ser al menos 0.',
            'promedio2.required' => 'El campo promedio2 es obligatorio.',
            'promedio2.numeric' => 'El campo promedio2 debe ser un número.',
            'promedio2.min' => 'El campo promedio2 debe ser al menos 0.',
            'desviacion2.required' => 'El campo desviacion2 es obligatorio.',
            'desviacion2.numeric' => 'El campo desviacion2 debe ser un número.',
            'desviacion2.min' => 'El campo desviacion2 debe ser al menos 0.',
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
