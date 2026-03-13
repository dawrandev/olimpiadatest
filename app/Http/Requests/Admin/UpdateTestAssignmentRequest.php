<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id' => 'required|exists:groups,id',
            'subject_id' => 'required|exists:subjects,id',
            'language_id' => 'required|exists:languages,id',
            'duration' => 'required|integer|min:5|max:180',
            'question_count' => 'required|integer|min:5|max:100',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'group_id.required' => __('Group is required'),
            'subject_id.required' => __('Subject is required'),
            'language_id.required' => __('Language is required'),
            'duration.required' => __('Duration is required'),
            'duration.min' => __('Duration must be at least :min minutes', ['min' => 5]),
            'duration.max' => __('Duration must not exceed :max minutes', ['max' => 180]),
            'question_count.required' => __('Question count is required'),
            'question_count.min' => __('Question count must be at least :min', ['min' => 5]),
            'question_count.max' => __('Question count must not exceed :max', ['max' => 100]),
            'start_time.required' => __('Start time is required'),
            'end_time.required' => __('End time is required'),
            'end_time.after' => __('End time must be after start time'),
        ];
    }

    public function attributes(): array
    {
        return [
            'group_id' => __('group'),
            'subject_id' => __('subject'),
            'language_id' => __('language'),
            'duration' => __('duration'),
            'question_count' => __('question count'),
            'start_time' => __('start time'),
            'end_time' => __('end time'),
            'description' => __('description'),
        ];
    }
}
