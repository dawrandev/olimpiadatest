<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SubjectStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.*' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => __('The subject names field is required.'),
            'name.array'      => __('The subject names must be an array.'),
            'name.*.required' => __('The subject name field is required.'),
            'name.*.string'   => __('The subject name must be a valid string.'),
            'name.*.max'      => __('The subject name may not be greater than :max characters.'),
        ];
    }
}
