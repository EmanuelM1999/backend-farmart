<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConsultarKardexRequest extends FormRequest
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

            'tipo_movimiento' => [
                'sometimes',
                'string',
                Rule::in([
                    'entrada',
                    'salida',
                    'salida_por_traslado',
                    'entrada_por_traslado',
                    'ajuste',
                ]),
            ],

            'desde' => [
                'sometimes',
                'date',
                'before_or_equal:hasta',
            ],

            'hasta' => [
                'sometimes',
                'date',
                'after_or_equal:desde',
            ],

            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'bodega_id.exists' => 'La bodega seleccionada no existe.',
            'medicamento_id.exists' => 'El medicamento seleccionado no existe.',
            'lote_id.exists' => 'El lote seleccionado no existe.',
            'tipo_movimiento.in' => 'El tipo de movimiento no es válido.',
            'desde.before_or_equal' => 'La fecha inicial no puede ser posterior a la fecha final.',
            'hasta.after_or_equal' => 'La fecha final no puede ser anterior a la fecha inicial.',
            'per_page.max' => 'No se permiten más de 100 registros por página.',
        ];
    }
}
