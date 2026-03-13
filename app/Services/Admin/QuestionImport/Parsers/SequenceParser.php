<?php

namespace App\Services\Admin\QuestionImport\Parsers;

use Illuminate\Support\Facades\Log;

class SequenceParser implements ParserInterface
{
    public function canParse(string $line, array $context): bool
    {
        return ($context['current_question_type'] ?? null) === 'sequence';
    }

    public function parse(string $line, array &$currentQuestion, array &$context): void
    {
        $lineNum = $context['line_num'] ?? 0;
        $answerVariantsStarted = $context['answer_variants_started'] ?? false;
        $sequenceItems = $context['sequence_items'] ?? [];

        // Parse sequence answer: Ответ: [1, 2, 3] yoki Javob: [1, 2, 3] yoki Answer: [1, 2, 3]
        if (!$answerVariantsStarted && preg_match('/(ответ|javob|answer)\s*:\s*\[([^\]]+)\]/ui', $line, $matches)) {
            $orderString = trim($matches[2]);
            $orderArray = array_map(function ($v) {
                return (int)trim($v);
            }, explode(',', $orderString));

            // Har bir sequence item ga order berish
            foreach ($sequenceItems as $index => $item) {
                if (isset($orderArray[$index])) {
                    $currentQuestion['answers'][] = [
                        'text' => $item['text'],
                        'is_correct' => true,
                        'order' => $orderArray[$index],
                        'original_text' => $item['original_text'],
                    ];
                }
            }

            $context['answer_variants_started'] = true;
            return;
        }

        // Parse sequence items (1) variant, 2) variant)
        if (!$answerVariantsStarted && preg_match('/^(\d+)\)\s*(.+)$/u', $line, $matches)) {
            $key = trim($matches[1]);
            $text = trim($matches[2]);

            // Agar bu javob emas (Ответ/Javob so'zi yo'q)
            if (!preg_match('/(ответ|javob|answer)\s*:/ui', $text)) {
                $context['sequence_items'][] = [
                    'key' => $key,
                    'text' => $text,
                    'original_text' => $line,
                ];
                return;
            }
        }

        // Continue question text
        if (!$answerVariantsStarted && !empty($line)) {
            if (!preg_match('/^\d+\)/', $line) && !preg_match('/(ответ|javob|answer)\s*:/ui', $line)) {
                $currentQuestion['text'] .= ' ' . $line;
            }
        }
    }

    public function getType(): string
    {
        return 'sequence';
    }

    public function finalizeQuestion(array $question, array $context): array
    {
        // Context dan ma'lumotlarni tozalash kerak emas
        return $question;
    }
}
