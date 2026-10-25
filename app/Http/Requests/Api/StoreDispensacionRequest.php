<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDispensacionRequest extends FormRequest
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
            'paciente_id' => [
                'required',
                'integer',
                'exists:pacientes,id',
            ],

            'prescripcion_id' => [
                'required',
                'integer',
                'exists:prescripciones,id',
            ],

            'bodega_id' => [
                'required',
                'integer',
                'exists:bodegas,id',
            ],

            'medicamentos' => [
                'required',
                'array',
                'min:1',
            ],

            'medicamentos.*' => [
                'required',
                'array:prescripcion_detalle_id,cantidad',
            ],

            'medicamentos.*.prescripcion_detalle_id' => [
                'required',
                'integer',
                'distinct',
                'exists:prescripcion_detalles,id',
            ],

            'medicamentos.*.cantidad' => [
                'required',
                'integer',
                'min:1',
            ],

            'clave_idempotencia' => [
                'required',
                'uuid',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'paciente_id.required' => 'Debes seleccionar un paciente.',
            'prescripcion_id.required' => 'Debes indicar la prescripción.',
            'bodega_id.required' => 'Debes seleccionar la bodega de salida.',
            'medicamentos.required' => 'Debes incluir al menos un medicamento.',
            'medicamentos.min' => 'Debes incluir al menos un medicamento.',
            'medicamentos.*.array' => 'Cada medicamento debe incluir su detalle de prescripción y cantidad.',
            'medicamentos.*.prescripcion_detalle_id.exists' => 'Uno de los detalles de prescripción no existe.',
            'medicamentos.*.prescripcion_detalle_id.distinct' => 'No puedes repetir un detalle de prescripción.',
            'medicamentos.*.cantidad.min' => 'La cantidad a dispensar debe ser mayor que cero.',
            'clave_idempotencia.required' => 'Debes enviar una clave de idempotencia.',
            'clave_idempotencia.uuid' => 'La clave de idempotencia debe tener formato UUID.',
        ];
    }
}
