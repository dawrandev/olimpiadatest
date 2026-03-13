<?php

namespace App\Services\Admin\QuestionImport\Validators;

class SequenceValidator implements ValidatorInterface
{
    public function validate(array $question): array
    {
        $questionNumber = $question['number'] ?? 'N/A';

        // 1. Savol matni tekshiruvi
        if (empty($question['text'])) {
            return ['valid' => false, 'message' => "Savol #{$questionNumber} [SEQUENCE]: Savol matni topilmadi"];
        }

        $questionText = trim($question['text']);

        // 2. Savol matni yetarli uzunlikda emasligini tekshirish
        $cleanQuestionText = trim(preg_replace('/[.?:;,!]/', '', $questionText));
        if (strlen($cleanQuestionText) < 10) {
            return ['valid' => false, 'message' => "Savol #{$questionNumber} [SEQUENCE]: Savol matni juda qisqa"];
        }

        // 3. Savol matni oxirida ":" bo'lishi kerak (format ko'rsatmasiga ko'ra)
        if (!str_ends_with($questionText, ':')) {
            return ['valid' => false, 'message' => "Savol #{$questionNumber} [SEQUENCE]: Savol matni oxirida ':' bo'lishi kerak"];
        }

        // 4. Javoblar mavjudligini tekshirish
        if (empty($question['answers'])) {
            return ['valid' => false, 'message' => "Savol #{$questionNumber} [SEQUENCE]: Javob elementlari topilmadi"];
        }

        // 5. Minimal elementlar soni (kamida 2 ta)
        if (count($question['answers']) < 2) {
            return ['valid' => false, 'message' => "Savol #{$questionNumber} [SEQUENCE]: Kamida 2 ta element bo'lishi kerak. Topilgan: " . count($question['answers'])];
        }

        // 6. Maksimal elementlar soni (10 tadan ko'p bo'lmasligi kerak)
        if (count($question['answers']) > 10) {
            return ['valid' => false, 'message' => "Savol #{$questionNumber} [SEQUENCE]: Elementlar soni 10 tadan oshmasligi kerak. Topilgan: " . count($question['answers'])];
        }

        // 7. Har bir elementda 'order' mavjudligini tekshirish
        foreach ($question['answers'] as $index => $answer) {
            if (!isset($answer['order'])) {
                return ['valid' => false, 'message' => "Savol #{$questionNumber} [SEQUENCE]: Element #" . ($index + 1) . " uchun 'order' topilmadi"];
            }

            if (!is_numeric($answer['order']) || $answer['order'] < 1) {
                return ['valid' => false, 'message' => "Savol #{$questionNumber} [SEQUENCE]: Element #" . ($index + 1) . " uchun noto'g'ri 'order' qiymati: {$answer['order']}"];
            }
        }

        // 8. Har bir elementda matn mavjudligini tekshirish
        foreach ($question['answers'] as $index => $answer) {
            if (empty($answer['text'])) {
                return ['valid' => false, 'message' => "Savol #{$questionNumber} [SEQUENCE]: Element #" . ($index + 1) . " matni bo'sh"];
            }

            $cleanText = trim(strip_tags($answer['text']));
            if (empty($cleanText)) {
                return ['valid' => false, 'message' => "Savol #{$questionNumber} [SEQUENCE]: Element #" . ($index + 1) . " matni bo'sh (HTML teglardan tozalangandan keyin)"];
            }

            if (strlen($cleanText) < 2) {
                return ['valid' => false, 'message' => "Savol #{$questionNumber} [SEQUENCE]: Element #" . ($index + 1) . " matni juda qisqa"];
            }
        }

        // 9. Order raqamlarini olish va saralash
        $orders = array_column($question['answers'], 'order');
        $originalOrders = $orders;
        sort($orders);

        $expectedCount = count($orders);

        // 10. 1 dan N gacha barcha raqamlar mavjudligini tekshirish
        for ($i = 1; $i <= $expectedCount; $i++) {
            if (!in_array($i, $orders)) {
                return [
                    'valid' => false,
                    'message' => "Savol #{$questionNumber} [SEQUENCE]: {$i} raqami javobda yo'q. Kutilgan: [1, 2, 3, ..., {$expectedCount}]. Topilgan: [" . implode(', ', $originalOrders) . "]"
                ];
            }
        }

        // 11. Takrorlanuvchi order raqamlari yo'qligini tekshirish
        if (count($orders) !== count(array_unique($orders))) {
            $duplicates = array_diff_assoc($orders, array_unique($orders));
            return [
                'valid' => false,
                'message' => "Savol #{$questionNumber} [SEQUENCE]: Takrorlanuvchi raqamlar mavjud: [" . implode(', ', array_unique($duplicates)) . "]"
            ];
        }

        // 12. Order raqamlari elementlar soniga mos kelishini tekshirish
        $maxOrder = max($orders);
        if ($maxOrder !== $expectedCount) {
            return [
                'valid' => false,
                'message' => "Savol #{$questionNumber} [SEQUENCE]: Order raqamlari elementlar soniga mos kelmaydi. Elementlar soni: {$expectedCount}, maksimal order: {$maxOrder}"
            ];
        }

        // 13. Har bir elementda original_text mavjudligini tekshirish
        foreach ($question['answers'] as $index => $answer) {
            if (!isset($answer['original_text'])) {
                return [
                    'valid' => false,
                    'message' => "Savol #{$questionNumber} [SEQUENCE]: Element #" . ($index + 1) . " uchun 'original_text' topilmadi"
                ];
            }
        }

        // 14. Original text formatini tekshirish (1) text, 2) text, ...)
        foreach ($question['answers'] as $index => $answer) {
            $originalText = trim($answer['original_text']);

            if (!preg_match('/^\d+\)\s+.+/u', $originalText)) {
                return [
                    'valid' => false,
                    'message' => "Savol #{$questionNumber} [SEQUENCE]: Element #" . ($index + 1) . " formati noto'g'ri. Kutilgan: 'N) text'. Topilgan: '{$originalText}'"
                ];
            }
        }

        // 15. Takroriy matnlar yo'qligini tekshirish
        $texts = [];
        foreach ($question['answers'] as $index => $answer) {
            $normalizedText = mb_strtolower(trim(strip_tags($answer['text'])));

            if (in_array($normalizedText, $texts)) {
                return [
                    'valid' => false,
                    'message' => "Savol #{$questionNumber} [SEQUENCE]: Takroriy element matni topildi: '{$answer['text']}'"
                ];
            }

            $texts[] = $normalizedText;
        }

        // 16. Barcha elementlar is_correct = true ekanligini tekshirish
        foreach ($question['answers'] as $index => $answer) {
            if (!isset($answer['is_correct']) || $answer['is_correct'] !== true) {
                return [
                    'valid' => false,
                    'message' => "Savol #{$questionNumber} [SEQUENCE]: Element #" . ($index + 1) . " uchun 'is_correct' true bo'lishi kerak"
                ];
            }
        }

        return ['valid' => true, 'message' => ''];
    }

    public function getType(): string
    {
        return 'sequence';
    }
}
