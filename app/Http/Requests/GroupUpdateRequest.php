<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupUpdateRequest extends FormRequest
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
            'faculty_id' => ['required', 'exists:faculties,id'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'faculty_id.required' => __('validation.faculty_required'),
            'faculty_id.exists'   => __('validation.faculty_exists'),
            'name.required'       => __('validation.group_name_required'),
            'name.string'         => __('validation.group_name_string'),
            'name.max'            => __('validation.group_name_max'),
        ];
    }
}
