<?php

// SingleChoiceParser.php ni BUTUNLAY almashtiring

namespace App\Services\Admin\QuestionImport\Parsers;

use Illuminate\Support\Facades\Log;

class SingleChoiceParser implements ParserInterface
{
    public function canParse(string $line, array $context): bool
    {
        $currentType = $context['current_question_type'] ?? 'single_choice';
        return $currentType === 'single_choice';
    }

    public function parse(string $line, array &$currentQuestion, array &$context): void
    {
        // Use ORIGINAL line with HTML tags preserved
        $originalLine = $context['original_line'] ?? $line;
        $lineNum = $context['line_num'] ?? 0;

        // Parse answer options (a), b), c), d) yoki A., B., C.
        // MUHIM: originalLine dan match qilish, HTML taglar bilan
        if (preg_match('/^\*?([a-zA-Z])[\.\)]\s*(.*)$/u', $line, $matches)) {
            $letter = $matches[1];

            // Answer text should come from ORIGINAL line to preserve HTML
            $answerText = $matches[2];

            // But if originalLine is different from line, use originalLine
            if ($originalLine !== $line) {
                // Extract answer text from original line with HTML
                if (preg_match('/^\*?[a-zA-Z][\.\)]\s*(.*)$/u', $originalLine, $origMatches)) {
                    $answerText = $origMatches[1];
                }
            }

            $isCorrect = str_starts_with(trim($originalLine), '*');

            $currentQuestion['answers'][] = [
                'text' => $answerText, // This now includes HTML tags like <math>
                'is_correct' => $isCorrect,
                'original_text' => $originalLine,
            ];

            return;
        }

        // Continue text - append to last answer or question
        if (!empty($line)) {
            if (empty($currentQuestion['answers'])) {
                // Append to question text, use original line to preserve HTML
                $currentQuestion['text'] .= ' ' . $originalLine;
            } else {
                // Append to last answer text
                $lastIndex = count($currentQuestion['answers']) - 1;
                $currentQuestion['answers'][$lastIndex]['text'] .= ' ' . $originalLine;
            }
        }
    }

    public function getType(): string
    {
        return 'single_choice';
    }

    public function finalizeQuestion(array $question, array $context): array
    {
        // Single choice uchun maxsus finalization kerak emas
        return $question;
    }
}
