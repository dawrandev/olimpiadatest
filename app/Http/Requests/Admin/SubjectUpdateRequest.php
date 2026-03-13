<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SubjectUpdateRequest extends FormRequest
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

    public function messages(): array
    {
        return [
            'translations.required'        => __('The subject translations field is required.'),
            'translations.array'           => __('The subject translations must be an array.'),

            'translations.*.name.required' => __('The subject name field is required.'),
            'translations.*.name.string'   => __('The subject name must be a valid string.'),
            'translations.*.name.max'      => __('The subject name may not be greater than :max characters.'),
        ];
    }
}
