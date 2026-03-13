<?php

namespace App\Services\Admin\QuestionImport\Validators;

interface ValidatorInterface
{
    /**
     * Validate question data
     * 
     * @param array $question
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validate(array $question): array;

    /**
     * Get the question type this validator handles
     */
    public function getType(): string;
}
