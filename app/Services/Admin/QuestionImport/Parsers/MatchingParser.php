<?php

namespace App\Services\Admin\QuestionImport\Parsers;

class MatchingParser implements ParserInterface
{
    public function canParse(string $line, array $context): bool
    {
        return ($context['current_question_type'] ?? null) === 'matching';
    }

    public function parse(string $line, array &$currentQuestion, array &$context): void
    {
        $originalLine = $context['original_line'] ?? $line;
        $lineNum = $context['line_num'] ?? 0;
        $answerVariantsStarted = $context['answer_variants_started'] ?? false;

        if (!$answerVariantsStarted && preg_match('/^(.+):\s*$/u', $line, $matches)) {
            $titleText = trim($matches[1]);

            if (empty($context['left_items']) && empty($context['left_items_title'])) {
                $context['left_items_title'] = $titleText . ':';
                return;
            }

            if (!empty($context['left_items']) && empty($context['right_items']) && empty($context['right_items_title'])) {
                $context['right_items_title'] = $titleText . ':';
                return;
            }
        }

        if (!$answerVariantsStarted && preg_match('/^([А-ЯA-Z]+)\.\s*(.+)$/u', $line, $matches)) {
            $key = trim($matches[1]);
            $text = trim($matches[2]);
            $text = rtrim($text, ';');

            if (preg_match('/^[А-ЯA-Z]+$/u', $key) && mb_strlen($key) <= 2) {
                $context['left_items'][] = ['key' => $key, 'text' => $text];
                return;
            }
        }

        if (preg_match('/^\*?(\d+)\)\s*(.+)$/u', $line, $matches)) {
            $key = trim($matches[1]);
            $text = trim($matches[2]);
            $text = rtrim($text, ';');
            $isCorrect = str_starts_with(trim($originalLine), '*');

            $hasMatchingPattern = preg_match('/[А-ЯA-Z0-9]+-[А-ЯA-Z0-9]+/', $text);

            if ($hasMatchingPattern || $answerVariantsStarted) {
                $context['answer_variants_started'] = true;

                $currentQuestion['answers'][] = [
                    'text' => $text,
                    'is_correct' => $isCorrect,
                    'original_text' => $line,
                ];
                return;
            }

            $context['right_items'][] = ['key' => $key, 'text' => $text];
            return;
        }

        if (!$answerVariantsStarted && !empty($line)) {
            if (
                !preg_match('/^[А-ЯA-Z]+\.\s/', $line) &&
                !preg_match('/^\*?\d+\)\s/', $line) &&
                !preg_match('/:\s*$/', $line)
            ) {
                $currentQuestion['text'] .= ' ' . $line;
            }
        }
    }

    public function getType(): string
    {
        return 'matching';
    }

    public function finalizeQuestion(array $question, array $context): array
    {
        $question['left_items'] = $context['left_items'] ?? [];
        $question['right_items'] = $context['right_items'] ?? [];
        $question['left_items_title'] = $context['left_items_title'] ?? null;
        $question['right_items_title'] = $context['right_items_title'] ?? null;

        return $question;
    }
}
