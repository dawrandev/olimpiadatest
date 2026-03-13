<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FacultyStoreRequest extends FormRequest
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
        $rules = [];

        foreach (getLanguages() as $language) {
            $rules['name.' . $language->id] = 'required|string|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [];

        foreach (getLanguages() as $language) {
            $messages['name.' . $language->id . '.required'] = "The faculty name in {$language->name} is required.";
            $messages['name.' . $language->id . '.string']   = "The faculty name in {$language->name} must be a valid string.";
            $messages['name.' . $language->id . '.max']      = "The faculty name in {$language->name} must not exceed 255 characters.";
        }

        return $messages;
    }
}
