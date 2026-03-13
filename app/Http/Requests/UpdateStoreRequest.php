<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'translations' => ['required', 'array'],
            'translations.*.name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'translations.required' => 'Translations are required.',
            'translations.array' => 'Translations must be an array.',
            'translations.*.name.required' => 'The faculty name is required for each language.',
            'translations.*.name.string' => 'The faculty name must be a string.',
            'translations.*.name.max' => 'The faculty name must not exceed 255 characters.',
        ];
    }
}
