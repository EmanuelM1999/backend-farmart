<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTrasladoRequest extends FormRequest
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
            'bodega_origen_id' => [
                'required',
                'integer',
                'exists:bodegas,id',
            ],

            'bodega_destino_id' => [
                'required',
                'integer',
                'different:bodega_origen_id',
                'exists:bodegas,id',
            ],

            'detalles' => [
                'required',
                'array',
                'min:1',
            ],

            'detalles.*' => [
                'required',
                'array:lote_id,cantidad_solicitada',
            ],

            'detalles.*.lote_id' => [
                'required',
                'integer',
                'distinct',
                'exists:lotes,id',
            ],

            'detalles.*.cantidad_solicitada' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'bodega_destino_id.different' => 'La bodega de destino debe ser diferente de la bodega de origen.',
            'detalles.required' => 'Debes incluir al menos un medicamento en el traslado.',
            'detalles.*.lote_id.distinct' => 'No puedes repetir el mismo lote en una solicitud.',
        ];
    }
}
