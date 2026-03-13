<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TopicUpdateRequest extends FormRequest
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
            'subject_id' => ['required', 'exists:subjects,id'],
            'translations' => ['required', 'array'],
            'translations.*.name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.required' => __('The subject field is required.'),
            'subject_id.exists'   => __('The selected subject is invalid.'),
            'translations.required' => __('Translations are required.'),
            'translations.array' => __('Translations must be an array.'),
            'translations.*.name.required' => __('The topic name is required for each language.'),
            'translations.*.name.string'   => __('The topic name must be a string.'),
            'translations.*.name.max'      => __('The topic name may not be greater than 255 characters.'),
        ];
    }
}
