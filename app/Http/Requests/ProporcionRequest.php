<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProporcionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'x' => ['required','integer','min:0'],
            'n' => ['required','integer','min:1'],
            'p0' => ['required','numeric','min:0','max:1'],
            'alpha' => ['nullable','numeric','min:0','max:1'],
            'tail' => ['nullable','in:two,left,right'],
            'continuity' => ['nullable','boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $data = $this->only(['x','n']);
            if (isset($data['x']) && isset($data['n']) && $data['x'] > $data['n']) {
                $v->errors()->add('x', 'El número de éxitos (x) no puede ser mayor que el tamaño de muestra n.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'x.required' => 'El número de éxitos (x) es requerido.',
            'n.required' => 'El tamaño de la muestra (n) es requerido.',
            'p0.required' => 'La proporción nula p0 es requerida.',
            'p0.numeric' => 'p0 debe ser un número entre 0 y 1.',
            'alpha.numeric' => 'alpha debe ser un número entre 0 y 1.',
            'tail.in' => 'El parámetro tail debe ser uno de: two, left, right.',
        ];
    }
}
