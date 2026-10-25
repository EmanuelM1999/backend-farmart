<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecibirTrasladoRequest extends FormRequest
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
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*' => ['required', 'array:traslado_detalle_id,cantidad_recibida'],
            'detalles.*.traslado_detalle_id' => [
                'required',
                'integer',
                'distinct',
                'exists:traslado_detalles,id',
            ],
            'detalles.*.cantidad_recibida' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'detalles.required' => 'Debes indicar los detalles recibidos.',
            'detalles.min' => 'Debes enviar al menos un detalle.',
            'detalles.*.traslado_detalle_id.exists' => 'Uno de los detalles no existe.',
            'detalles.*.traslado_detalle_id.distinct' => 'No puedes repetir un detalle.',
            'detalles.*.cantidad_recibida.min' => 'La cantidad recibida no puede ser negativa.',
        ];
    }
}
