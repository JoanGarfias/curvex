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
            "values" => ["required", "string", new Coords2Rule()]
        ];
    }

    public function messages(): array
    {
        return [
            'values.required' => 'Debe proporcionar una cadena con coordenadas.',
            'values.string' => 'Las coordenadas deben ser una cadena de texto.',
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
}
