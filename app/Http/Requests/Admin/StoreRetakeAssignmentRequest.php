<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRetakeAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|exists:students,id',
            'question_count' => 'required|integer|min:5|max:100',
            'duration' => 'required|integer|min:5|max:180',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'student_ids.required' => __('Please select at least one student'),
            'student_ids.min' => __('Please select at least one student'),
            'student_ids.*.exists' => __('Selected student is invalid'),
            'question_count.required' => __('Question count is required'),
            'question_count.min' => __('Question count must be at least :min', ['min' => 5]),
            'question_count.max' => __('Question count must not exceed :max', ['max' => 100]),
            'duration.required' => __('Duration is required'),
            'duration.min' => __('Duration must be at least :min minutes', ['min' => 5]),
            'duration.max' => __('Duration must not exceed :max minutes', ['max' => 180]),
            'start_time.required' => __('Start time is required'),
            'start_time.after' => __('Start time must be in the future'),
            'end_time.required' => __('End time is required'),
            'end_time.after' => __('End time must be after start time'),
        ];
    }

    public function attributes(): array
    {
        return [
            'student_ids' => __('students'),
            'question_count' => __('question count'),
            'duration' => __('duration'),
            'start_time' => __('start time'),
            'end_time' => __('end time'),
            'description' => __('description'),
        ];
    }
}
