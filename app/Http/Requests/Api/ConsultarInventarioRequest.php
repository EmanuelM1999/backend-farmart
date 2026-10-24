<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConsultarInventarioRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bodega_id' => [
                'sometimes',
                'integer',
                'min:1',
                'exists:bodegas,id',
            ],

            'medicamento_id' => [
                'sometimes',
                'integer',
                'min:1',
                'exists:medicamentos,id',
            ],

            'lote_id' => [
                'sometimes',
                'integer',
                'min:1',
                'exists:lotes,id',
            ],

            'vencimiento_proximo' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'bodega_id.integer' => 'El identificador de la bodega debe ser un número entero.',
            'bodega_id.exists' => 'La bodega seleccionada no existe.',
            'medicamento_id.integer' => 'El identificador del medicamento debe ser un número entero.',
            'medicamento_id.exists' => 'El medicamento seleccionado no existe.',
            'lote_id.integer' => 'El identificador del lote debe ser un número entero.',
            'lote_id.exists' => 'El lote seleccionado no existe.',
            'vencimiento_proximo.boolean' => 'El filtro de vencimiento debe ser verdadero o falso.',
        ];
    }
}
