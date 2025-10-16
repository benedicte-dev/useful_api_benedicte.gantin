<?php
// app/Http/Requests/ShortenUrlRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShortenUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'original_url' => 'required|url',
            'custom_code' => [
                'nullable',
                'string',
                'max:10',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('short_links', 'code')
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'original_url.required' => 'L\'URL originale est obligatoire.',
            'original_url.url' => 'L\'URL doit être valide (commencer par http:// ou https://).',
            'custom_code.max' => 'Le code personnalisé ne peut pas dépasser 10 caractères.',
            'custom_code.regex' => 'Le code personnalisé ne peut contenir que des lettres, chiffres, tirets et underscores.',
            'custom_code.unique' => 'Ce code personnalisé est déjà utilisé.',
        ];
    }
}
