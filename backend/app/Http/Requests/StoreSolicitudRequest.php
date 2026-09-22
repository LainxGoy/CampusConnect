<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_solicitud' => 'required|in:Mantenimiento,Soporte Tecnológico,Infraestructura,Equipamiento',
            'titulo' => 'required|string|min:5|max:150',
            'descripcion' => 'required|string|min:15',
            'ubicacion' => 'required|string|max:100',
            'prioridad_estimada' => 'required|in:Baja,Media,Alta,Crítica',
            'evidencia' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // Máximo 10MB
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_solicitud.required' => 'El tipo de solicitud es obligatorio.',
            'tipo_solicitud.in' => 'El tipo de solicitud seleccionado no es válido.',
            'titulo.required' => 'El título es obligatorio.',
            'titulo.min' => 'El título debe tener al menos 5 caracteres.',
            'descripcion.required' => 'La descripción detallada es obligatoria.',
            'descripcion.min' => 'La descripción debe tener al menos 15 caracteres.',
            'ubicacion.required' => 'Debe especificar la ubicación o aula.',
            'prioridad_estimada.required' => 'La prioridad estimada es obligatoria.',
            'evidencia.mimes' => 'El archivo adjunto debe ser en formato JPG, PNG o PDF.',
            'evidencia.max' => 'El archivo adjunto no puede exceder los 10MB.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Errores de validación en la solicitud.',
            'errors' => $validator->errors()
        ], 422));
    }
}
