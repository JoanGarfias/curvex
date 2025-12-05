<?php

namespace App\Http\Requests;

class MuestroAceptacionInput extends ApiFormRequest
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
            'AQT' => 'required|numeric|min:0|max:1',
            'LTPD' => 'required|numeric|min:0|max:1',
            '1-alpha' => 'required|numeric|min:0|max:1',
            'beta' => 'required|numeric|min:0|max:1',
        ];
    }

    public function messages(): array
    {
        return [
            'AQT.required' => 'El campo AQT es obligatorio.',
            'AQT.numeric' => 'El campo AQT debe ser un número.',
            'AQT.min' => 'El campo AQT debe ser al menos 0.',
            'AQT.max' => 'El campo AQT no debe ser mayor que 1.',
            'LTPD.required' => 'El campo LTPD es obligatorio.',
            'LTPD.numeric' => 'El campo LTPD debe ser un número.',
            'LTPD.min' => 'El campo LTPD debe ser al menos 0.',
            'LTPD.max' => 'El campo LTPD no debe ser mayor que 1.',
            '1-alpha.required' => 'El campo 1-alpha es obligatorio.',
            '1-alpha.numeric' => 'El campo 1-alpha debe ser un número.',
            '1-alpha.min' => 'El campo 1-alpha debe ser al menos 0.',
            '1-alpha.max' => 'El campo 1-alpha no debe ser mayor que 1.',
            'beta.required' => 'El campo beta es obligatorio.',
            'beta.numeric' => 'El campo beta debe ser un número.',
            'beta.min' => 'El campo beta debe ser al menos 0.',
            'beta.max' => 'El campo beta no debe ser mayor que 1.',
        ];
    }
}
