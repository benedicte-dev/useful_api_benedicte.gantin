<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du produit est obligatoire.',
            'price.required' => 'Le prix est obligatoire.',
            'price.min' => 'Le prix doit être supérieur à 0.',
            'stock.required' => 'Le stock est obligatoire.',
            'stock.min' => 'Le stock ne peut pas être négatif.',
            'min_stock.required' => 'Le stock minimum est obligatoire.',
            'min_stock.min' => 'Le stock minimum ne peut pas être négatif.',
        ];
    }
}
