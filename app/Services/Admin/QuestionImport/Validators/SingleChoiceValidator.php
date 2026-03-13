<?php

namespace App\Services\Admin\QuestionImport\Validators;

class SingleChoiceValidator implements ValidatorInterface
{
    public function validate(array $question): array
    {
        $questionNumber = $question['number'] ?? 'N/A';

        // 1. Savol matni tekshiruvi
        if (empty($question['text'])) {
            return ['valid' => false, 'message' => "Savol #{$questionNumber}: Savol matni topilmadi"];
        }

        $questionText = trim($question['text']);

        // 2. Savol matni uzunligi (minimal 10 belgi, tinish belgilarini hisobga olgan holda)
        $cleanQuestionText = trim(preg_replace('/[.?:;,!]/', '', $questionText));
        if (strlen($cleanQuestionText) < 10) {
            return ['valid' => false, 'message' => "Savol #{$questionNumber}: Savol matni juda qisqa"];
        }

        // 3. Javob variantlari mavjudligi
        if (empty($question['answers'])) {
            return ['valid' => false, 'message' => "Savol #{$questionNumber}: Javob variantlari topilmadi"];
        }

        // 4. Javob variantlari soni (2-10)
        if (count($question['answers']) < 2) {
            return ['valid' => false, 'message' => "Savol #{$questionNumber}: Kamida 2 ta javob varianti bo'lishi kerak"];
        }

        if (count($question['answers']) > 10) {
            return ['valid' => false, 'message' => "Savol #{$questionNumber}: Javob variantlari soni 10 tadan oshmasligi kerak"];
        }

        // 5. To'g'ri javoblar sonini tekshirish (kamida 1 ta)
        $correctAnswersCount = 0;
        foreach ($question['answers'] as $answer) {
            if ($answer['is_correct']) {
                $correctAnswersCount++;
            }
        }

        if ($correctAnswersCount === 0) {
            return ['valid' => false, 'message' => "Savol #{$questionNumber}: Kamida 1 ta to'g'ri javob (*) belgisi bo'lishi kerak"];
        }

        // Barcha javoblar to'g'ri bo'lmasligi kerak
        if ($correctAnswersCount === count($question['answers'])) {
            return ['valid' => false, 'message' => "Savol #{$questionNumber}: Barcha javoblar to'g'ri bo'lishi mumkin emas. Kamida 1 ta noto'g'ri javob bo'lishi kerak"];
        }

        // 6. Har bir javob variantini tekshirish
        foreach ($question['answers'] as $index => $answer) {
            if (empty($answer['text'])) {
                return ['valid' => false, 'message' => "Savol #{$questionNumber}: Bo'sh javob varianti mavjud"];
            }

            $cleanText = strip_tags($answer['text']);
            $cleanText = trim($cleanText);

            if (empty($cleanText)) {
                return ['valid' => false, 'message' => "Savol #{$questionNumber}, Variant " . chr(65 + $index) . ": Bo'sh javob varianti mavjud"];
            }

            if (strlen($cleanText) < 1) {
                return ['valid' => false, 'message' => "Savol #{$questionNumber}, Variant " . chr(65 + $index) . ": Javob matni juda qisqa"];
            }
        }

        // 7. Javob formatini tekshirish
        try {
            $this->validateAnswerFormat($question['answers'], $questionNumber);
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }

        // 8. Takroriy javoblarni tekshirish
        $answerTexts = [];
        foreach ($question['answers'] as $index => $answer) {
            $normalizedText = mb_strtolower(trim(strip_tags($answer['text'])));

            if (in_array($normalizedText, $answerTexts)) {
                return ['valid' => false, 'message' => "Savol #{$questionNumber}, Variant " . chr(65 + $index) . ": Takroriy javob topildi"];
            }

            $answerTexts[] = $normalizedText;
        }

        // 9. Javob harflarining ketma-ketligini tekshirish
        $expectedLetters = range('A', chr(64 + count($question['answers'])));
        foreach ($question['answers'] as $index => $answer) {
            $originalText = $answer['original_text'] ?? '';
            $textToCheck = ltrim($originalText, '*');
            $textToCheck = trim($textToCheck);

            if (!empty($textToCheck)) {
                $firstChar = mb_strtoupper(mb_substr($textToCheck, 0, 1));
                $expectedLetter = $expectedLetters[$index];

                if ($firstChar !== $expectedLetter && $firstChar !== mb_strtolower($expectedLetter)) {
                    return ['valid' => false, 'message' => "Savol #{$questionNumber}: Javob harflari ketma-ketligi buzilgan. Kutilgan: {$expectedLetter}, topilgan: {$firstChar}"];
                }
            }
        }

        return ['valid' => true, 'message' => ''];
    }

    public function getType(): string
    {
        return 'single_choice';
    }

    /**
     * Validate answer format (a), b), c) or A., B., C.)
     */
    public function validateAnswerFormat(array $answers, $questionNumber): void
    {
        foreach ($answers as $index => $answer) {
            $originalText = $answer['original_text'] ?? null;

            if (!$originalText) {
                throw new \Exception(
                    "Savol #{$questionNumber}, Variant " . chr(65 + $index) .
                        ": Original text topilmadi"
                );
            }

            $textToCheck = strip_tags($originalText, '<span><math>');
            $textToCheck = ltrim($textToCheck, '*');
            $textToCheck = trim($textToCheck);

            if (empty($textToCheck)) {
                throw new \Exception(
                    "Savol #{$questionNumber}, Variant " . chr(65 + $index) .
                        ": Javob matni bo'sh"
                );
            }

            $expectedLetterLower = chr(97 + $index); // a, b, c...
            $expectedLetterUpper = chr(65 + $index); // A, B, C...

            $pattern = '/^[' . $expectedLetterLower . $expectedLetterUpper . '][\.\)]\s+/u';

            if (!preg_match($pattern, $textToCheck)) {
                throw new \Exception(
                    "Savol #{$questionNumber}, Variant " . chr(65 + $index) .
                        ": Javob formati noto'g'ri. '{$expectedLetterLower})' yoki '{$expectedLetterUpper}.' formatida bo'lishi kerak. " .
                        "Topilgan: '" . mb_substr($textToCheck, 0, 10) . "...'"
                );
            }

            if (!preg_match('/^[a-zA-Z][\.\)]\s+.+/u', $textToCheck)) {
                throw new \Exception(
                    "Savol #{$questionNumber}, Variant " . chr(65 + $index) .
                        ": Harf va javob matni orasida probel bo'lishi kerak"
                );
            }
        }
    }
}
