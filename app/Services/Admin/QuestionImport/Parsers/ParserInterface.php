<?php

namespace App\Services\Admin\QuestionImport\Parsers;

interface ParserInterface
{
    /**
     * Check if this parser can handle the current line/context
     */
    public function canParse(string $line, array $context): bool;

    /**
     * Parse the line and update question data
     */
    public function parse(string $line, array &$currentQuestion, array &$context): void;

    /**
     * Get the question type this parser handles
     */
    public function getType(): string;

    /**
     * Finalize the question before saving
     */
    public function finalizeQuestion(array $question, array $context): array;
}
