<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_solicitud' => 'sometimes|in:Mantenimiento,Soporte Tecnológico,Infraestructura,Equipamiento',
            'titulo' => 'sometimes|string|min:5|max:150',
            'descripcion' => 'sometimes|string|min:15',
            'ubicacion' => 'sometimes|string|max:100',
            'prioridad_estimada' => 'sometimes|in:Baja,Media,Alta,Crítica',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Errores de validación en la actualización.',
            'errors' => $validator->errors()
        ], 422));
    }
}
