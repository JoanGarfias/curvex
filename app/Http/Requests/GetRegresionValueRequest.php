<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetRegresionValueRequest extends FormRequest
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
            'method' => ['required', 'string', 'in:lineal,exponential,potential,cuadratic'],
            'variable_input' => ['required', 'string', 'in:x,y'],
            'value' => ['required', 'numeric'],
            'solutions' => ['required', 'array', 'min:2'],
            'solutions.*' => ['required', 'numeric']
        ];
    }

    public function messages(): array
    {
        return [
            'method.required' => 'Debe especificar el método de regresión.',
            'method.in' => 'El método debe ser: lineal, exponential, potential o cuadratic.',
            'variable_input.required' => 'Debe especificar qué variable desea calcular (x o y).',
            'variable_input.in' => 'La variable debe ser "x" o "y".',
            'value.required' => 'Debe proporcionar el valor conocido.',
            'value.numeric' => 'El valor debe ser numérico.',
            'solutions.required' => 'Debe proporcionar el array de soluciones.',
            'solutions.array' => 'Las soluciones deben ser un array.',
            'solutions.min' => 'Debe proporcionar al menos 2 soluciones (intercepto y pendiente).',
            'solutions.*.numeric' => 'Cada solución debe ser un valor numérico.',
        ];
    }

    /**
     * Ensure validation failures always return JSON response.
     */
    protected function failedValidation(Validator $validator): void
    {
        $response = response()->json([
            'message' => 'Datos incorrectos.',
            'errors' => $validator->errors(),
        ], 422);

        throw new HttpResponseException($response);
    }
}
