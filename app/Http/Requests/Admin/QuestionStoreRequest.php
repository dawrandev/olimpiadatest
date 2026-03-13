<?php

namespace App\\Http\\Requests\\Admin;

use Illuminate\Foundation\Http\FormRequest;

class QuestionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language_id' => 'required|exists:languages,id',
            'subject_id' => 'required|exists:subjects,id',
            'topic_id' => 'required|exists:topics,id',
            'file' => 'required|file|mimes:docx,html|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'language_id.required' => __('Language is required'),
            'language_id.exists' => __('Invalid language selected'),
            'subject_id.required' => __('Subject is required'),
            'subject_id.exists' => __('Invalid subject selected'),
            'topic_id.required' => __('Topic is required'),
            'topic_id.exists' => __('Invalid topic selected'),
            'file.required' => __('File upload is required'),
            'file.mimes' => __('Only .docx files are accepted'),
            'file.max' => __('File size must not exceed 10MB'),
        ];
    }
}
