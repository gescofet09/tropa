<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0.01'], 
            'stock' => ['required', 'integer', 'min:1'],     
            'categoria_id' => ['required', 'exists:categorias,id'],
            'unidad' => ['required', 'string'],
        ];
    }
}
