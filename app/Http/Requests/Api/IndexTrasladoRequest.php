<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexTrasladoRequest extends FormRequest
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
            'estado' => [
                'sometimes',
                'string',
                'in:BORRADOR,SOLICITADO,APROBADO,EN_TRANSITO,RECIBIDO,RECIBIDO_PARCIAL,ANULADO',
            ],
            'bodega_origen_id' => [
                'sometimes',
                'integer',
                'exists:bodegas,id',
            ],
            'bodega_destino_id' => [
                'sometimes',
                'integer',
                'exists:bodegas,id',
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
            'estado.string' => 'El estado del traslado debe ser una cadena de texto.',
            'estado.in' => 'El estado debe ser BORRADOR, SOLICITADO, APROBADO, EN_TRANSITO, RECIBIDO, RECIBIDO_PARCIAL o ANULADO.',
            'bodega_origen_id.integer' => 'El identificador de la bodega de origen debe ser un número entero.',
            'bodega_origen_id.exists' => 'La bodega de origen seleccionada no existe.',
            'bodega_destino_id.integer' => 'El identificador de la bodega de destino debe ser un número entero.',
            'bodega_destino_id.exists' => 'La bodega de destino seleccionada no existe.',
            'per_page.integer' => 'La cantidad de resultados por página debe ser un número entero.',
            'per_page.min' => 'La cantidad mínima de resultados por página es 1.',
            'per_page.max' => 'La cantidad máxima de resultados por página es 100.',
        ];
    }
}
