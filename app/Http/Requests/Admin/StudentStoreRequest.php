<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StudentStoreRequest extends FormRequest
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
            'group_id' => ['required', 'exists:groups,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:50', 'unique:users,login'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'faculty_id.required' => __('Faculty is required.'),
            'faculty_id.exists' => __('Selected faculty does not exist.'),

            'group_id.required' => __('Group is required.'),
            'group_id.exists' => __('Selected group does not exist.'),

            'full_name.required' => __('Full name is required.'),
            'full_name.max' => __('Full name may not be greater than 255 characters.'),


            'login.required' => __('Login is required.'),
            'login.unique' => __('This login is already taken.'),
            'login.max' => __('Login may not be greater than 50 characters.'),

            'password.required' => __('Password is required.'),
            'password.min' => __('Password must be at least 6 characters.'),
            'password.confirmed' => __('Password confirmation does not match.'),
        ];
    }
}
