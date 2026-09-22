<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreComentarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contenido' => 'required|string|min:2|max:1000'
        ];
    }

    public function messages(): array
    {
        return [
            'contenido.required' => 'El contenido del comentario es obligatorio.',
            'contenido.min' => 'El comentario debe contener al menos 2 caracteres.',
            'contenido.max' => 'El comentario no puede exceder los 1000 caracteres.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Errores de validación en el comentario.',
            'errors' => $validator->errors()
        ], 422));
    }
}
