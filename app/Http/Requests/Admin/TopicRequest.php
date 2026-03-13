<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TopicRequest extends FormRequest
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
        $rules = [
            'subject_id' => 'required|exists:subjects,id',
        ];

        foreach (getLanguages() as $language) {
            $rules["translations.{$language->id}.name"] = 'required|string|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'subject_id.required' => __('The :attribute field is required.', ['attribute' => __('subject')]),
            'subject_id.exists'   => __('The selected :attribute is invalid.', ['attribute' => __('subject')]),

            'translations.*.name.required' => __('The :attribute field is required.', ['attribute' => __('topic name')]),
            'translations.*.name.string'   => __('The :attribute must be a string.', ['attribute' => __('topic name')]),
            'translations.*.name.max'      => __('The :attribute may not be greater than :max characters.', ['attribute' => __('topic name'), 'max' => 255]),
        ];
    }
}
