<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'score' => 'required|integer|min:0|max:100',
            'grade' => 'nullable|integer|min:2|max:5'
        ];
    }

    public function messages(): array
    {
        return [
            'score.required' => __('Score is required'),
            'score.integer' => __('Score must be a number'),
            'score.min' => __('Score must be at least :min', ['min' => 0]),
            'score.max' => __('Score must not exceed :max', ['max' => 100]),
            'grade.integer' => __('Grade must be a number'),
            'grade.min' => __('Grade must be at least :min', ['min' => 2]),
            'grade.max' => __('Grade must not exceed :max', ['max' => 5]),
        ];
    }

    public function attributes(): array
    {
        return [
            'score' => __('score'),
            'grade' => __('grade'),
        ];
    }
}
