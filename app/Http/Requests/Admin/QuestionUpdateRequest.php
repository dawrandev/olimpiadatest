<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class QuestionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_current_image' => 'nullable|in:0,1',
            'answers' => 'required|array|min:2',
            'answers.*.text' => 'required|string|max:500',
            'answers.*.id' => 'nullable|exists:answers,id',
            'correct_answer' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'text.required' => __('Question text is required'),
            'text.string' => __('Question text must be a string'),
            'text.max' => __('Question text must not exceed 1000 characters'),

            'image.image' => __('Uploaded file must be an image'),
            'image.mimes' => __('Only jpeg, png, jpg, gif formats are allowed'),
            'image.max' => __('Image size must not exceed 2MB'),

            'remove_current_image.in' => __('Invalid value for image removal flag'),

            'answers.required' => __('At least two answers are required'),
            'answers.array' => __('Answers must be in array format'),
            'answers.min' => __('At least two answers are required'),
            'answers.*.text.required' => __('Answer text is required'),
            'answers.*.text.string' => __('Answer text must be a string'),
            'answers.*.text.max' => __('Answer text must not exceed 500 characters'),
            'answers.*.id.exists' => __('Invalid answer ID'),

            'correct_answer.required' => __('Correct answer index is required'),
            'correct_answer.integer' => __('Correct answer index must be an integer'),
            'correct_answer.min' => __('Correct answer index must be at least 0'),
        ];
    }
}
