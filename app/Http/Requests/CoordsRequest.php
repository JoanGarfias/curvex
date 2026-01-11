<?php

namespace App\Http\Requests;

use App\Rules\Coords2Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CoordsRequest extends FormRequest
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
            "dependent" => ["required", "string"],
            "independent" => ["required", "array", "min:1"],
            "independent.*" => ["required", "string"],
            "method" => ["sometimes", "string"]
        ];
    }

    public function messages(): array
    {
        return [
            'dependent.required' => 'Debe proporcionar los datos de la variable dependiente (y).',
            'dependent.string' => 'Los datos de la variable dependiente deben ser una cadena de texto.',
            'independent.required' => 'Debe proporcionar al menos una variable independiente.',
            'independent.array' => 'Las variables independientes deben ser un array.',
            'independent.min' => 'Debe proporcionar al menos una variable independiente.',
            'independent.*.string' => 'Cada variable independiente debe ser una cadena de texto.',
        ];
    }

        /**
     * Ensure validation failures always return JSON response.
     * This forces a 422 JSON response even when the client did not send
     * an Accept: application/json header (common when testing with curl).
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        $response = response()->json([
            'message' => 'Datos incorrectos.',
            'errors' => $validator->errors(),
        ], 422);

        throw new HttpResponseException($response);
    }

    /**
     * Validar que todas las variables independientes y dependiente 
     * tengan la misma cantidad de datos.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $dependent = $this->input('dependent');
            $independent = $this->input('independent', []);

            if (!is_string($dependent) || empty($dependent)) {
                return;
            }

            $dependent_count = count(explode(',', trim($dependent)));

            foreach ($independent as $index => $ind_variable) {
                if (!is_string($ind_variable) || empty($ind_variable)) {
                    $validator->errors()->add(
                        "independent.{$index}",
                        "La variable independiente no puede estar vacía."
                    );
                    continue;
                }

                $ind_count = count(explode(',', trim($ind_variable)));

                if ($ind_count !== $dependent_count) {
                    $validator->errors()->add(
                        "independent.{$index}",
                        "La variable independiente #{$index} tiene {$ind_count} datos, " .
                        "pero la variable dependiente tiene {$dependent_count}. " .
                        "Todas deben tener la misma cantidad de datos."
                    );
                }
            }
        });

        return $this;
    }
}
