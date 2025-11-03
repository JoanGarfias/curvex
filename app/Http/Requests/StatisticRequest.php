<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NumberList;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StatisticRequest extends FormRequest
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
            // Either an uploaded file (Excel/CSV) or a string with numbers is required
            'file' => ['required_without:values', 'nullable', 'file', 'mimes:xlsx,xls,csv', 'max:5120'], // max 5MB
            'values' => ['required_without:file', 'nullable', 'string', new NumberList()],
            'varianza' => 'boolean',
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required_without' => 'Debe subir un archivo Excel/CSV o proporcionar una cadena de valores.',
            'file.file' => 'El archivo subido debe ser un archivo válido.',
            'file.mimes' => 'El archivo debe ser de tipo: xlsx, xls o csv.',
            'file.max' => 'El archivo no debe ser mayor a 5MB.',
            'values.required_without' => 'Debe proporcionar una cadena con valores o subir un archivo.',
            'values.string' => 'Los valores deben ser una cadena de texto.',
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
