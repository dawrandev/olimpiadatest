<?php

namespace App\Services\Admin\QuestionImport\Validators;

class MatchingValidator implements ValidatorInterface
{
    /**
     * Lotin harflarini kiril harflarga o'zgartirish
     */
    private function normalizeToRussian(string $text): string
    {
        $latinToCyrillic = [
            'A' => 'А',
            'B' => 'Б',
            'C' => 'В', // C = В (Ve)
            'D' => 'Г',
            'E' => 'Д',
            'F' => 'Е', // Har xil moslik
            'G' => 'Ж',
            'H' => 'З',
            'a' => 'а',
            'b' => 'б',
            'c' => 'в',
            'd' => 'г',
            'e' => 'д',
            'f' => 'е',
            'g' => 'ж',
            'h' => 'з'
        ];

        return strtr($text, $latinToCyrillic);
    }

    /**
     * Harfni normalizatsiya qilish (kiril yoki lotindan bitta formatga)
     */
    private function normalizeKey(string $key): string
    {
        // Lotin -> Kiril moslashtirish
        $mapping = [
            'A' => 'А',
            'a' => 'а',
            'B' => 'Б',
            'b' => 'б',
            'C' => 'В',
            'c' => 'в',
            'D' => 'Г',
            'd' => 'г',
            'E' => 'Д',
            'e' => 'д',
            'F' => 'Е',
            'f' => 'е',
            'G' => 'Ж',
            'g' => 'ж',
            'H' => 'З',
            'h' => 'з'
        ];

        return $mapping[$key] ?? $key;
    }

    public function validate(array $question): array
    {
        $questionNumber = $question['number'] ?? 'N/A';

        // 1. Savol matni tekshiruvi
        if (empty($question['text'])) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: Savol matni topilmadi\n\n" .
                    "SABAB: Savol raqamidan keyin savol matni yozilmagan\n\n" .
                    "TO'G'RI FORMAT:\n" .
                    "[MATCHING]\n" .
                    "1. Установите соответствие между типом боли и заболеванием.\n" .
                    "Тип боли:\n" .
                    "А. острая колющая;\n" .
                    "..."
            ];
        }

        $questionText = trim($question['text']);

        // 2. Savol matni uzunligi
        $cleanQuestionText = trim(preg_replace('/[.?:;,!]/', '', $questionText));
        if (strlen($cleanQuestionText) < 10) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: Savol matni juda qisqa\n\n" .
                    "SABAB: Savol kamida 10 ta harfdan iborat bo'lishi kerak\n\n" .
                    "TOPILGAN: '{$questionText}'\n\n" .
                    "TO'G'RILASH: Savol matnini to'liqroq yozing"
            ];
        }

        // 3. Chap tomon elementlari mavjudligi
        if (empty($question['left_items'])) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: Chap tomon elementlari topilmadi\n\n" .
                    "SABAB: А., Б., В. harflari bilan elementlar yozilmagan\n\n" .
                    "TO'G'RI FORMAT:\n" .
                    "Тип боли:\n" .
                    "А. острая колющая;\n" .
                    "Б. тупая ноющая;\n" .
                    "В. жгучая загрудинная;\n\n" .
                    "DIQQAT: Harf va nuqta orasida probel bo'lmasligi kerak: А. (to'g'ri), А . (noto'g'ri)"
            ];
        }

        // 4. Chap tomon elementlari soni - minimum
        if (count($question['left_items']) < 2) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: Chap tomonda juda kam element\n\n" .
                    "TOPILGAN: " . count($question['left_items']) . " ta element\n" .
                    "KERAK: Kamida 2 ta element\n\n" .
                    "TO'G'RILASH: Yana " . (2 - count($question['left_items'])) . " ta element qo'shing:\n" .
                    "А. birinchi element;\n" .
                    "Б. ikkinchi element;"
            ];
        }

        // 5. Chap tomon elementlari soni - maksimum (8 ta)
        if (count($question['left_items']) > 8) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: Chap tomonda juda ko'p element\n\n" .
                    "TOPILGAN: " . count($question['left_items']) . " ta element\n" .
                    "MAKSIMAL: 8 ta element (А, Б, В, Г, Д, Е, Ж, З)\n\n" .
                    "TO'G'RILASH: " . (count($question['left_items']) - 8) . " ta elementni olib tashlang"
            ];
        }

        // 6. O'ng tomon elementlari mavjudligi
        if (empty($question['right_items'])) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: O'ng tomon elementlari topilmadi\n\n" .
                    "SABAB: 1), 2), 3) raqamlar bilan elementlar yozilmagan\n\n" .
                    "TO'G'RI FORMAT:\n" .
                    "Заболевание:\n" .
                    "1) стенокардия;\n" .
                    "2) пневмоторакс;\n" .
                    "3) холецистит;\n\n" .
                    "DIQQAT: Raqamdan keyin qavs bo'lishi kerak: 1) (to'g'ri), 1. (noto'g'ri)"
            ];
        }

        // 7. O'ng tomon elementlari soni - minimum
        if (count($question['right_items']) < 2) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: O'ng tomonda juda kam element\n\n" .
                    "TOPILGAN: " . count($question['right_items']) . " ta element\n" .
                    "KERAK: Kamida 2 ta element\n\n" .
                    "TO'G'RILASH: Yana " . (2 - count($question['right_items'])) . " ta element qo'shing:\n" .
                    "1) birinchi element;\n" .
                    "2) ikkinchi element;"
            ];
        }

        // 8. O'ng tomon elementlari soni - maksimum (15 ta)
        if (count($question['right_items']) > 15) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: O'ng tomonda juda ko'p element\n\n" .
                    "TOPILGAN: " . count($question['right_items']) . " ta element\n" .
                    "MAKSIMAL: 15 ta element\n\n" .
                    "TO'G'RILASH: " . (count($question['right_items']) - 15) . " ta elementni olib tashlang"
            ];
        }

        // 9. Left items sarlavhasi
        if (empty($question['left_items_title'])) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: Chap tomon sarlavhasi topilmadi\n\n" .
                    "SABAB: А., Б., В. elementlaridan oldin sarlavha yozilmagan\n\n" .
                    "TO'G'RI FORMAT:\n" .
                    "Тип боли:  ← Bu sarlavha\n" .
                    "А. острая колющая;\n" .
                    "Б. тупая ноющая;\n\n" .
                    "DIQQAT: Sarlavha oxirida ikki nuqta (:) bo'lishi shart"
            ];
        }

        // 10. Right items sarlavhasi
        if (empty($question['right_items_title'])) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: O'ng tomon sarlavhasi topilmadi\n\n" .
                    "SABAB: 1), 2), 3) elementlaridan oldin sarlavha yozilmagan\n\n" .
                    "TO'G'RI FORMAT:\n" .
                    "Заболевание:  ← Bu sarlavha\n" .
                    "1) стенокардия;\n" .
                    "2) пневмоторакс;\n\n" .
                    "DIQQAT: Sarlavha oxirida ikki nuqta (:) bo'lishi shart"
            ];
        }

        // 11. Sarlavhalar ":" bilan tugashi
        if (!str_ends_with(trim($question['left_items_title']), ':')) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: Chap tomon sarlavhasi noto'g'ri\n\n" .
                    "TOPILGAN: '{$question['left_items_title']}'\n\n" .
                    "SABAB: Sarlavha oxirida ikki nuqta (:) yo'q\n\n" .
                    "TO'G'RILASH:\n" .
                    "NOTO'G'RI: Тип боли\n" .
                    "TO'G'RI: Тип боли:"
            ];
        }

        if (!str_ends_with(trim($question['right_items_title']), ':')) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: O'ng tomon sarlavhasi noto'g'ri\n\n" .
                    "TOPILGAN: '{$question['right_items_title']}'\n\n" .
                    "SABAB: Sarlavha oxirida ikki nuqta (:) yo'q\n\n" .
                    "TO'G'RILASH:\n" .
                    "NOTO'G'RI: Заболевание\n" .
                    "TO'G'RI: Заболевание:"
            ];
        }

        // Rus va lotin alifbosining birinchi 8 harfi
        $validRussianLetters = ['А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ж', 'З'];
        $validLatinLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $allValidLetters = array_merge($validRussianLetters, $validLatinLetters);

        // 12. Chap tomon elementlarini tekshirish
        $leftKeys = [];
        $leftKeysNormalized = []; // Normalizatsiya qilingan harflar

        foreach ($question['left_items'] as $index => $item) {
            if (empty($item['key'])) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: Chap tomon " . ($index + 1) . "-element uchun harf topilmadi\n\n" .
                        "TO'G'RI FORMAT:\n" .
                        "А. birinchi element;\n" .
                        "Б. ikkinchi element;\n\n" .
                        "DIQQAT: Harf va nuqta orasida probel bo'lmasligi kerak"
                ];
            }

            // Harf formatini tekshirish (faqat 1 ta harf, katta harf)
            if (!preg_match('/^[А-ЯA-Z]$/u', $item['key'])) {
                $expectedLetters = implode(', ', array_slice($validRussianLetters, 0, min(count($question['left_items']), 8)));
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: Chap tomon elementning harf formati noto'g'ri\n\n" .
                        "TOPILGAN: '{$item['key']}'\n" .
                        "KERAK: Faqat bitta KATTA harf\n\n" .
                        "TO'G'RI: {$expectedLetters} (rus harflari) yoki A, B, C, D, E, F, G, H (lotin harflari)\n\n" .
                        "NOTO'G'RI:\n" .
                        "- а (kichik harf)\n" .
                        "- 1 (raqam)\n" .
                        "- АБ (ikki harf)\n" .
                        "- И, К, Л (8 tadan keyingi harflar)"
                ];
            }

            // Harf to'g'ri diapazondan ekanligini tekshirish
            if (!in_array($item['key'], $allValidLetters)) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: Chap tomon elementi uchun noto'g'ri harf\n\n" .
                        "TOPILGAN: '{$item['key']}'\n\n" .
                        "SABAB: Faqat birinchi 8 ta harfdan foydalanish mumkin\n\n" .
                        "RUS HARFLARI: А, Б, В, Г, Д, Е, Ж, З (birinchi 8 ta)\n" .
                        "LOTIN HARFLARI: A, B, C, D, E, F, G, H (birinchi 8 ta)\n\n" .
                        "TO'G'RILASH: '{$item['key']}' o'rniga birinchi 8 tadan birini ishlating"
                ];
            }

            if (empty($item['text'])) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: '{$item['key']}' elementi uchun matn yo'q\n\n" .
                        "TO'G'RI FORMAT:\n" .
                        "{$item['key']}. bu yerda matn bo'lishi kerak;\n\n" .
                        "DIQQAT: Harf va nuqtadan keyin probel va matn yozilishi shart"
                ];
            }

            $cleanText = trim(strip_tags($item['text']));
            if (empty($cleanText)) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: '{$item['key']}' elementi matni bo'sh\n\n" .
                        "TOPILGAN: '{$item['text']}'\n\n" .
                        "SABAB: Matn faqat formatlanish belgilaridan iborat\n\n" .
                        "TO'G'RILASH: Normal matn yozing"
                ];
            }

            if (strlen($cleanText) < 2) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: '{$item['key']}' elementi matni juda qisqa\n\n" .
                        "TOPILGAN: '{$cleanText}' (" . strlen($cleanText) . " belgi)\n" .
                        "KERAK: Kamida 2 ta belgi\n\n" .
                        "TO'G'RILASH: Matnni to'liqroq yozing"
                ];
            }

            // Normalizatsiya qilingan harfni tekshirish
            $normalizedKey = $this->normalizeKey($item['key']);

            if (in_array($normalizedKey, $leftKeysNormalized)) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: Bir xil harf ikki marta ishlatilgan\n\n" .
                        "TAKRORIY HARF: '{$item['key']}'\n\n" .
                        "SABAB: Har bir element uchun boshqa harf ishlatilishi kerak\n\n" .
                        "TO'G'RILASH:\n" .
                        "NOTO'G'RI:\n" .
                        "А. birinchi\n" .
                        "А. ikkinchi ← takroriy\n\n" .
                        "TO'G'RI:\n" .
                        "А. birinchi\n" .
                        "Б. ikkinchi"
                ];
            }

            $leftKeys[] = $item['key'];
            $leftKeysNormalized[] = $normalizedKey;
        }

        // 13. O'ng tomon elementlarini tekshirish
        $rightKeys = [];
        foreach ($question['right_items'] as $index => $item) {
            if (empty($item['key'])) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: O'ng tomon " . ($index + 1) . "-element uchun raqam topilmadi\n\n" .
                        "TO'G'RI FORMAT:\n" .
                        "1) birinchi element;\n" .
                        "2) ikkinchi element;\n\n" .
                        "DIQQAT: Raqamdan keyin qavs bo'lishi kerak"
                ];
            }

            if (!preg_match('/^\d+$/', $item['key'])) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: O'ng tomon elementning raqam formati noto'g'ri\n\n" .
                        "TOPILGAN: '{$item['key']}'\n" .
                        "KERAK: 1, 2, 3, 4...\n\n" .
                        "TO'G'RILASH:\n" .
                        "NOTO'G'RI: а (harf), 1а (aralash)\n" .
                        "TO'G'RI: 1, 2, 3, 4"
                ];
            }

            if (empty($item['text'])) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: '{$item['key']})' elementi uchun matn yo'q\n\n" .
                        "TO'G'RI FORMAT:\n" .
                        "{$item['key']}) bu yerda matn bo'lishi kerak;\n\n" .
                        "DIQQAT: Raqam va qavsdan keyin probel va matn yozilishi shart"
                ];
            }

            $cleanText = trim(strip_tags($item['text']));
            if (empty($cleanText)) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: '{$item['key']})' elementi matni bo'sh\n\n" .
                        "TOPILGAN: '{$item['text']}'\n\n" .
                        "SABAB: Matn faqat formatlanish belgilaridan iborat\n\n" .
                        "TO'G'RILASH: Normal matn yozing"
                ];
            }

            if (strlen($cleanText) < 2) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: '{$item['key']})' elementi matni juda qisqa\n\n" .
                        "TOPILGAN: '{$cleanText}' (" . strlen($cleanText) . " belgi)\n" .
                        "KERAK: Kamida 2 ta belgi\n\n" .
                        "TO'G'RILASH: Matnni to'liqroq yozing"
                ];
            }

            if (in_array($item['key'], $rightKeys)) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: Bir xil raqam ikki marta ishlatilgan\n\n" .
                        "TAKRORIY RAQAM: '{$item['key']})'\n\n" .
                        "SABAB: Har bir element uchun boshqa raqam ishlatilishi kerak\n\n" .
                        "TO'G'RILASH:\n" .
                        "NOTO'G'RI:\n" .
                        "1) birinchi\n" .
                        "1) ikkinchi ← takroriy\n\n" .
                        "TO'G'RI:\n" .
                        "1) birinchi\n" .
                        "2) ikkinchi"
                ];
            }
            $rightKeys[] = $item['key'];
        }

        // 14-16. Javob variantlari
        if (empty($question['answers'])) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: Javob variantlari topilmadi\n\n" .
                    "SABAB: Elementlardan keyin javob variantlari yozilmagan\n\n" .
                    "TO'G'RI FORMAT:\n" .
                    "1) А-1, Б-2, В-3, Г-4;\n" .
                    "*2) А-2, Б-4, В-1, Г-3;  ← to'g'ri javob\n" .
                    "3) А-3, Б-1, В-2, Г-4;\n\n" .
                    "DIQQAT:\n" .
                    "- Javoblar 1), 2), 3) formatida\n" .
                    "- To'g'ri javob * yulduzcha bilan\n" .
                    "- Kamida 2 ta javob varianti kerak"
            ];
        }

        if (count($question['answers']) < 2) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: Juda kam javob varianti\n\n" .
                    "TOPILGAN: " . count($question['answers']) . " ta\n" .
                    "KERAK: Kamida 2 ta\n\n" .
                    "TO'G'RILASH: Yana " . (2 - count($question['answers'])) . " ta javob varianti qo'shing\n\n" .
                    "MISOL:\n" .
                    "1) А-1, Б-2, В-3;\n" .
                    "*2) А-2, Б-1, В-3;"
            ];
        }

        if (count($question['answers']) > 10) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: Juda ko'p javob varianti\n\n" .
                    "TOPILGAN: " . count($question['answers']) . " ta\n" .
                    "MAKSIMAL: 10 ta\n\n" .
                    "TO'G'RILASH: " . (count($question['answers']) - 10) . " ta javob variantini olib tashlang"
            ];
        }

        // 17. To'g'ri javob
        $correctCount = 0;
        foreach ($question['answers'] as $answer) {
            if (!empty($answer['text']) && $answer['is_correct']) {
                $correctCount++;
            }
        }

        if ($correctCount === 0) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: To'g'ri javob belgilanmagan\n\n" .
                    "SABAB: Hech bir javob oldida * yulduzcha yo'q\n\n" .
                    "TO'G'RILASH:\n" .
                    "NOTO'G'RI:\n" .
                    "1) А-1, Б-2, В-3;\n" .
                    "2) А-2, Б-1, В-3;\n\n" .
                    "TO'G'RI:\n" .
                    "1) А-1, Б-2, В-3;\n" .
                    "*2) А-2, Б-1, В-3;  ← to'g'ri javob\n\n" .
                    "DIQQAT: * belgisi raqamdan oldin yoziladi"
            ];
        }

        if ($correctCount === count($question['answers'])) {
            return [
                'valid' => false,
                'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                    "XATOLIK: Barcha javoblar to'g'ri deb belgilangan\n\n" .
                    "TOPILGAN: {$correctCount} ta to'g'ri javob\n\n" .
                    "SABAB: Barcha javoblar oldida * belgisi bor\n\n" .
                    "TO'G'RILASH: Faqat bitta to'g'ri javobni * bilan belgilang\n\n" .
                    "MISOL:\n" .
                    "1) А-1, Б-2;  ← noto'g'ri\n" .
                    "*2) А-2, Б-1;  ← to'g'ri\n" .
                    "3) А-1, Б-1;  ← noto'g'ri"
            ];
        }

        // 18-20. Har bir javob variantini tekshirish
        foreach ($question['answers'] as $index => $answer) {
            $variantNum = $index + 1;

            if (empty($answer['text'])) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: {$variantNum}-javob varianti bo'sh\n\n" .
                        "TO'G'RI FORMAT:\n" .
                        "{$variantNum}) А-1, Б-2, В-3;"
                ];
            }

            $answerText = trim($answer['text']);

            // Format tekshiruvi
            $validPattern = '/^[А-ЯA-Z0-9]+-[А-ЯA-Z0-9]+([;,\s]+[А-ЯA-Z0-9]+-[А-ЯA-Z0-9]+)*[;,\s]*$/u';

            if (!preg_match($validPattern, $answerText)) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: {$variantNum}-javob varianti formati noto'g'ri\n\n" .
                        "TOPILGAN: '{$answerText}'\n\n" .
                        "TO'G'RI FORMATLAR:\n" .
                        "1) А-2, Б-1, В-3;  (vergul bilan)\n" .
                        "2) А-2; Б-1; В-3;  (nuqta-vergul bilan)\n" .
                        "3) 1-Б, 2-А, 3-В;  (raqam-harf)\n\n" .
                        "NOTO'G'RI:\n" .
                        "- А=1, Б=2  (= belgisi noto'g'ri)\n" .
                        "- А1, Б2  (tire yo'q)\n" .
                        "- A-1 B-2  (ajratuvchi yo'q)"
                ];
            }

            // Juftliklarni ajratish
            $pairs = preg_split('/[;,\s]+/', $answerText, -1, PREG_SPLIT_NO_EMPTY);

            if (count($pairs) < 1) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: {$variantNum}-javob variantida juftliklar topilmadi\n\n" .
                        "TO'G'RILASH: Har bir elementni moslang:\n" .
                        "А-1, Б-2, В-3"
                ];
            }

            $maxPairs = max(count($question['left_items']), count($question['right_items']));
            if (count($pairs) > $maxPairs) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: {$variantNum}-javob variantida juda ko'p juftlik\n\n" .
                        "TOPILGAN: " . count($pairs) . " ta juftlik\n" .
                        "MAKSIMAL: {$maxPairs} ta\n\n" .
                        "SABAB: Sizda " . count($question['left_items']) . " ta chap element va " .
                        count($question['right_items']) . " ta o'ng element bor"
                ];
            }

            $usedLeftKeys = [];
            $usedRightKeys = [];
            $pairsFormat = null;

            foreach ($pairs as $pairIndex => $pair) {
                $pair = trim($pair);

                if (!preg_match('/^([А-ЯA-Z0-9]+)-([А-ЯA-Z0-9]+)$/u', $pair, $matches)) {
                    return [
                        'valid' => false,
                        'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                            "XATOLIK: {$variantNum}-javob variantida noto'g'ri juftlik\n\n" .
                            "TOPILGAN: '{$pair}'\n\n" .
                            "TO'G'RI FORMATLAR:\n" .
                            "- А-2 (harf-tire-raqam)\n" .
                            "- 1-Б (raqam-tire-harf)\n\n" .
                            "NOTO'G'RI:\n" .
                            "- А2 (tire yo'q)\n" .
                            "- А-Б (ikkalasi ham harf)\n" .
                            "- 1-2 (ikkalasi ham raqam)"
                    ];
                }

                $firstKey = $matches[1];
                $secondKey = $matches[2];

                // Format aniqlash
                if ($pairIndex === 0) {
                    $isFirstLeft = preg_match('/^[А-ЯA-Z]$/u', $firstKey);
                    $isSecondRight = preg_match('/^\d+$/', $secondKey);

                    $isFirstRight = preg_match('/^\d+$/', $firstKey);
                    $isSecondLeft = preg_match('/^[А-ЯA-Z]$/u', $secondKey);

                    if ($isFirstLeft && $isSecondRight) {
                        $pairsFormat = 'left-right';
                    } elseif ($isFirstRight && $isSecondLeft) {
                        $pairsFormat = 'right-left';
                    } else {
                        return [
                            'valid' => false,
                            'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                                "XATOLIK: {$variantNum}-javob variantida noto'g'ri format\n\n" .
                                "TOPILGAN: '{$pair}'\n\n" .
                                "TO'G'RI: Harf va raqamni moslashtiring:\n" .
                                "- А-2 (harf-raqam) yoki\n" .
                                "- 1-Б (raqam-harf)"
                        ];
                    }
                }

                // Keylarni ajratish
                if ($pairsFormat === 'left-right') {
                    $leftKey = $firstKey;
                    $rightKey = $secondKey;

                    if (!preg_match('/^[А-ЯA-Z]$/u', $leftKey) || !preg_match('/^\d+$/', $rightKey)) {
                        return [
                            'valid' => false,
                            'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                                "XATOLIK: {$variantNum}-javobda format aralash\n\n" .
                                "TOPILGAN: '{$pair}'\n\n" .
                                "SABAB: Birinchi juftlik А-2 formatida, lekin bu juftlik boshqacha\n\n" .
                                "TO'G'RILASH: Barcha juftliklarni bir xil formatda yozing:\n" .
                                "А-2, Б-1, В-3  (hammasi harf-raqam)"
                        ];
                    }
                } else {
                    $rightKey = $firstKey;
                    $leftKey = $secondKey;

                    if (!preg_match('/^\d+$/', $rightKey) || !preg_match('/^[А-ЯA-Z]$/u', $leftKey)) {
                        return [
                            'valid' => false,
                            'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                                "XATOLIK: {$variantNum}-javobda format aralash\n\n" .
                                "TOPILGAN: '{$pair}'\n\n" .
                                "SABAB: Birinchi juftlik 1-Б formatida, lekin bu juftlik boshqacha\n\n" .
                                "TO'G'RILASH: Barcha juftliklarni bir xil formatda yozing:\n" .
                                "1-Б, 2-А, 3-В  (hammasi raqam-harf)"
                        ];
                    }
                }

                // MUHIM: Harflarni normalizatsiya qilib solishtirish
                $normalizedLeftKey = $this->normalizeKey($leftKey);
                $normalizedLeftKeysArray = array_map([$this, 'normalizeKey'], $leftKeys);

                // Key mavjudligini tekshirish (normalizatsiya qilingan)
                if (!in_array($normalizedLeftKey, $normalizedLeftKeysArray)) {
                    return [
                        'valid' => false,
                        'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                            "XATOLIK: {$variantNum}-javobda noto'g'ri harf\n\n" .
                            "TOPILGAN: '{$leftKey}'\n" .
                            "MAVJUD HARFLAR: " . implode(', ', $leftKeys) . "\n\n" .
                            "SABAB: '{$leftKey}' harfi chap tomon elementlarida yo'q\n\n" .
                            "TO'G'RILASH: Faqat mavjud harflardan foydalaning"
                    ];
                }

                if (!in_array($rightKey, $rightKeys)) {
                    return [
                        'valid' => false,
                        'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                            "XATOLIK: {$variantNum}-javobda noto'g'ri raqam\n\n" .
                            "TOPILGAN: '{$rightKey}'\n" .
                            "MAVJUD RAQAMLAR: " . implode(', ', $rightKeys) . "\n\n" .
                            "SABAB: '{$rightKey}' raqami o'ng tomon elementlarida yo'q\n\n" .
                            "TO'G'RILASH: Faqat mavjud raqamlardan foydalaning"
                    ];
                }

                // Takroriy key (normalizatsiya qilingan)
                $normalizedUsedLeftKeys = array_map([$this, 'normalizeKey'], $usedLeftKeys);

                if (in_array($normalizedLeftKey, $normalizedUsedLeftKeys)) {
                    return [
                        'valid' => false,
                        'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                            "XATOLIK: {$variantNum}-javobda takroriy harf\n\n" .
                            "TAKRORIY: '{$leftKey}'\n\n" .
                            "SABAB: Bir javob ichida bir harf faqat bir marta ishlatilishi mumkin\n\n" .
                            "NOTO'G'RI: А-1, Б-2, А-3  (А ikki marta)\n" .
                            "TO'G'RI: А-1, Б-2, В-3"
                    ];
                }
                $usedLeftKeys[] = $leftKey;
                $usedRightKeys[] = $rightKey;
            }

            // 21. Original text
            if (!isset($answer['original_text'])) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: Tizim xatosi - original_text yo'q\n\n" .
                        "YECHIM: Faylni qayta yuklang"
                ];
            }
        }

        $normalizedAnswers = [];
        foreach ($question['answers'] as $index => $answer) {
            $normalized = mb_strtolower(preg_replace('/[\s;,]+/', '', trim($answer['text'])));
            $normalized = $this->normalizeToRussian($normalized);

            if (in_array($normalized, $normalizedAnswers)) {
                return [
                    'valid' => false,
                    'message' => "❌ Savol #{$questionNumber} [MATCHING]\n\n" .
                        "XATOLIK: Bir xil javob ikki marta yozilgan\n\n" .
                        "TAKRORIY JAVOB: '{$answer['text']}'\n\n" .
                        "SABAB: Har bir javob varianti har xil bo'lishi kerak\n\n" .
                        "TO'G'RILASH: Takroriy javobni o'zgartiring yoki o'chiring"
                ];
            }

            $normalizedAnswers[] = $normalized;
        }

        return ['valid' => true, 'message' => ''];
    }

    public function getType(): string
    {
        return 'matching';
    }
}
