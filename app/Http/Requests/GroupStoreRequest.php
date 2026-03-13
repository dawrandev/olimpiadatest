<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupStoreRequest extends FormRequest
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
            'faculty_id' => 'required|exists:faculties,id',
            'name' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'faculty_id.required' => __('The faculty field is required.'),
            'faculty_id.exists'   => __('The selected faculty does not exist.'),
            'name.required'       => __('The group name is required.'),
            'name.string'         => __('The group name must be a string.'),
            'name.max'            => __('The group name must not exceed 255 characters.'),
        ];
    }
}
