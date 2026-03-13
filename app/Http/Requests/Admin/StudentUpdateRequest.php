<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentUpdateRequest extends FormRequest
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
            'group_id'   => [
                'required',
                'exists:groups,id',
                function ($attribute, $value, $fail) {
                    $facultyId = $this->input('faculty_id');
                    if ($facultyId && !\App\Models\Group::where('id', $value)->where('faculty_id', $facultyId)->exists()) {
                        $fail(__('The selected group does not belong to the chosen faculty.'));
                    }
                },
            ],
            'full_name'  => ['required', 'string', 'max:255'],
            'login' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'login')->ignore($this->getStudentUserId()),
            ],
            'password'   => ['nullable', 'string', 'min:6', 'confirmed'],
        ];
    }

    protected function getStudentUserId(): ?int
    {
        $studentId = $this->route('id') ?? $this->route('student');
        if ($studentId) {
            return \App\Models\Student::find($studentId)?->user_id;
        }
        return null;
    }


    public function messages(): array
    {
        return [
            'faculty_id.required' => __('Faculty is required'),
            'faculty_id.exists'   => __('Selected faculty is invalid'),
            'group_id.required'   => __('Group is required'),
            'group_id.exists'     => __('Selected group is invalid'),
            'full_name.required'  => __('Full name is required'),
            'login.required'      => __('Login is required'),
            'login.unique'        => __('This login is already taken'),
            'password.min'        => __('Password must be at least 6 characters'),
            'password.confirmed'  => __('Passwords do not match'),
        ];
    }
}
