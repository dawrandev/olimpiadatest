<?php

namespace App\Services\Admin;

use App\Repositories\Admin\QuestionRepository;
use App\Models\Question;
use App\Services\Admin\QuestionImport\DocumentProcessor;
use App\Services\Admin\QuestionImport\Parsers\SingleChoiceParser;
use App\Services\Admin\QuestionImport\Parsers\MatchingParser;
use App\Services\Admin\QuestionImport\Parsers\SequenceParser;
use App\Services\Admin\QuestionImport\Validators\SingleChoiceValidator;
use App\Services\Admin\QuestionImport\Validators\MatchingValidator;
use App\Services\Admin\QuestionImport\Validators\SequenceValidator;
use Illuminate\Support\Facades\DB;

class QuestionImportService
{
    protected $repository;
    protected $documentProcessor;
    protected $parsers = [];
    protected $validators = [];

    protected $errors = [];
    protected $uploadedCount = 0;
    protected $currentLanguageId;
    protected $currentSubjectId;
    protected $currentTopicId;

    public function __construct(QuestionRepository $repository)
    {
        $this->repository = $repository;
        $this->documentProcessor = new DocumentProcessor();

        $this->parsers = [
            'single_choice' => new SingleChoiceParser(),
            'matching' => new MatchingParser(),
            'sequence' => new SequenceParser(),
        ];

        $this->validators = [
            'single_choice' => new SingleChoiceValidator(),
            'matching' => new MatchingValidator(),
            'sequence' => new SequenceValidator(),
        ];
    }

    public function importFromDocx(string $filePath, array $data): array
    {
        $this->currentLanguageId = $data['language_id'];
        $this->currentSubjectId = $data['subject_id'];
        $this->currentTopicId = $data['topic_id'];

        $this->errors = [];
        $this->uploadedCount = 0;

        try {
            $customTempDir = storage_path('app/temp');

            if (!file_exists($customTempDir)) {
                mkdir($customTempDir, 0755, true);
            }

            putenv('TMPDIR=' . $customTempDir);
            putenv('TEMP=' . $customTempDir);
            putenv('TMP=' . $customTempDir);

            if (!str_ends_with(strtolower($filePath), '.docx')) {
                throw new \Exception('Faqat .docx formatdagi fayllar qabul qilinadi');
            }

            $html = $this->documentProcessor->extractContentAsHtml($filePath, $customTempDir);

            $questions = $this->parseQuestionsFromHtml($html);

            if (empty($questions)) {
                throw new \Exception('Hech qanday savol topilmadi. Fayl formatini tekshiring.');
            }

            $this->validateAllQuestions($questions);

            // YANGI: Takroriy savollarni tekshirish
            $this->checkDuplicateQuestions($questions);

            if (!empty($this->errors)) {
                return [
                    'success' => false,
                    'uploaded_count' => 0,
                    'error_count' => count($this->errors),
                    'errors' => $this->errors,
                ];
            }

            DB::beginTransaction();

            try {
                $this->saveQuestions($questions);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                $this->errors[] = [
                    'line' => 'Database',
                    'message' => 'Saqlashda xatolik: ' . $e->getMessage()
                ];
            }
        } catch (\Exception $e) {
            $this->errors[] = [
                'line' => 'File',
                'message' => $e->getMessage()
            ];
        }

        return [
            'success' => empty($this->errors) && $this->uploadedCount > 0,
            'uploaded_count' => $this->uploadedCount,
            'error_count' => count($this->errors),
            'errors' => $this->errors,
        ];
    }

    protected function checkDuplicateQuestions(array $questions): void
    {
        $seenQuestions = [];

        foreach ($questions as $index => $question) {
            $questionNumber = $question['number'] ?? ($index + 1);

            // Savol matnini normalizatsiya qilish
            $normalizedText = $this->normalizeText($question['text']);

            // Agar savol allaqachon mavjud bo'lsa
            if (isset($seenQuestions[$normalizedText])) {
                $this->errors[] = [
                    'line' => $questionNumber,
                    'message' => "❌ Takroriy savol topildi!\n\n" .
                        "Savol #{$questionNumber} va #{$seenQuestions[$normalizedText]} bir xil:\n" .
                        "'{$question['text']}'\n\n" .
                        "YECHIM: Takroriy savollardan birini o'chiring"
                ];
                continue;
            }

            $seenQuestions[$normalizedText] = $questionNumber;

            // Javob variantlari ichida takroriy variantlarni tekshirish
            if ($question['type'] === 'single_choice') {
                $this->checkDuplicateAnswers($question, $questionNumber);
            }
        }
    }

    protected function checkDuplicateAnswers(array $question, $questionNumber): void
    {
        if (empty($question['answers'])) {
            return;
        }

        $seenAnswers = [];

        foreach ($question['answers'] as $index => $answer) {
            $normalizedAnswer = $this->normalizeText($answer['text']);

            if (in_array($normalizedAnswer, $seenAnswers)) {
                $this->errors[] = [
                    'line' => $questionNumber,
                    'message' => "❌ Savol #{$questionNumber}: Takroriy javob varianti topildi!\n\n" .
                        "Variant " . chr(65 + $index) . ": '{$answer['text']}'\n\n" .
                        "SABAB: Bu javob varianti allaqachon mavjud\n\n" .
                        "YECHIM: Takroriy javob variantlarini o'zgartiring yoki o'chiring"
                ];
                return;
            }

            $seenAnswers[] = $normalizedAnswer;
        }
    }

    protected function normalizeText(string $text): string
    {
        $text = strip_tags($text);

        $text = mb_strtolower($text, 'UTF-8');

        $text = preg_replace('/[\s\.\?\!\,\;\:\-\(\)]+/u', '', $text);

        $text = trim($text);

        return $text;
    }

    protected function parseQuestionsFromHtml(string $html): array
    {
        if (substr_count($html, '<math') > 0) {
            $html = preg_replace('/<annotation[^>]*>.*?<\/annotation>/s', '', $html);
            $html = preg_replace('/<semantics>(.*?)<\/semantics>/s', '$1', $html);
            $html = preg_replace('/\\\\\(.*?\\\\\)/s', '', $html);
            $html = preg_replace('/\\\\\[.*?\\\\\]/s', '', $html);
            $html = preg_replace('/\\\\[a-zA-Z]+[\\_\\^]?\{[^}]*\}/s', '', $html);
            $html = preg_replace('/\\\\[a-zA-Z]+(?![a-zA-Z])/s', '', $html);
            $html = preg_replace('/<mrow>|<\/mrow>/i', '', $html);
        }

        $questions = [];

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            $lines = explode("\n", strip_tags($html, '<span><sup><sub><math><mi><mo><mn><mfrac><msup><msub><img>'));
        } else {
            $lines = $this->documentProcessor->extractLinesFromDom($body);
        }

        $currentQuestion = null;
        $context = [
            'next_question_type' => null,
            'current_question_type' => null,
            'answer_variants_started' => false,
            'left_items' => [],
            'right_items' => [],
            'sequence_items' => [],
            'left_items_title' => null,
            'right_items_title' => null,
        ];

        foreach ($lines as $lineNum => $line) {
            $originalLine = $line;
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            $context['line_num'] = $lineNum;
            $context['original_line'] = $originalLine;

            if (preg_match('/^\[MATCHING\]\s*$/i', $line)) {
                $context['next_question_type'] = 'matching';
                continue;
            }

            if (preg_match('/^\[SEQUENCE\]\s*$/i', $line)) {
                $context['next_question_type'] = 'sequence';
                continue;
            }

            $imageFile = null;
            if (preg_match('/data-image="([^"]+)"/', $line, $imgMatch)) {
                $imageFile = $imgMatch[1];
            }

            $line = preg_replace('/<img[^>]*>/i', '', $line);
            $line = trim($line);

            if (empty($line) && $imageFile && $currentQuestion) {
                $currentQuestion['image'] = $imageFile;
                continue;
            }

            if (preg_match('/^(.+\?)\s+(\*?[A-Z]\..+)$/u', $line, $inlineMatch)) {
                $questionPart = trim($inlineMatch[1]);
                $answersPart = trim($inlineMatch[2]);
                $answersArray = preg_split('/(?=\*?[A-Z]\.)/u', $answersPart, -1, PREG_SPLIT_NO_EMPTY);
                $line = $questionPart;
                foreach (array_reverse($answersArray) as $ans) {
                    $ans = trim($ans);
                    if (!empty($ans)) {
                        array_splice($lines, $lineNum + 1, 0, [$ans]);
                    }
                }
            }

            if (preg_match('/^(\d+)\.\s*(.+)$/u', $line, $matches)) {
                $questionNumber = $matches[1];
                $questionText = trim($matches[2]);
            } elseif (preg_match('/^([^*\dA-Z].+\?)$/u', $line, $matches) && !$currentQuestion) {
                $questionNumber = count($questions) + 1;
                $questionText = trim($matches[1]);
            } else {
                $matches = null;
            }

            if (isset($matches) && $matches) {
                if ($currentQuestion) {
                    $parser = $this->parsers[$currentQuestion['type']] ?? null;
                    if ($parser) {
                        $currentQuestion = $parser->finalizeQuestion($currentQuestion, $context);
                    }
                    $questions[] = $currentQuestion;
                }

                $questionType = $context['next_question_type'] ?? 'single_choice';
                if (isset($context['next_question_type'])) {
                    unset($context['next_question_type']);
                }

                $currentQuestion = [
                    'number' => $questionNumber,
                    'text' => $questionText,
                    'type' => $questionType,
                    'answers' => [],
                    'image' => $imageFile,
                ];
                $context['current_question_type'] = $questionType;
                $context['left_items'] = [];
                $context['right_items'] = [];
                $context['sequence_items'] = [];
                $context['answer_variants_started'] = false;
                $context['left_items_title'] = null;
                $context['right_items_title'] = null;

                continue;
            }

            if ($currentQuestion) {
                foreach ($this->parsers as $parser) {
                    if ($parser->canParse($line, $context)) {
                        $parser->parse($line, $currentQuestion, $context);
                        break;
                    }
                }
            }
        }

        if ($currentQuestion) {
            $parser = $this->parsers[$currentQuestion['type']] ?? null;
            if ($parser) {
                $currentQuestion = $parser->finalizeQuestion($currentQuestion, $context);
            }
            $questions[] = $currentQuestion;
        }

        foreach ($questions as $index => &$question) {
            $question['text'] = $this->cleanLatexFromText($question['text']);

            if (isset($question['answers']) && is_array($question['answers'])) {
                foreach ($question['answers'] as &$answer) {
                    if (isset($answer['text'])) {
                        $answer['text'] = $this->cleanLatexFromText($answer['text']);
                    }
                }
            }

            if (isset($question['left_items'])) {
                foreach ($question['left_items'] as &$item) {
                    $item['text'] = $this->cleanLatexFromText($item['text']);
                }
            }

            if (isset($question['right_items'])) {
                foreach ($question['right_items'] as &$item) {
                    $item['text'] = $this->cleanLatexFromText($item['text']);
                }
            }
        }

        return $questions;
    }

    protected function validateAllQuestions(array $questions): void
    {
        foreach ($questions as $index => $question) {
            $questionNumber = $question['number'] ?? ($index + 1);

            try {
                $validator = $this->validators[$question['type']] ?? null;

                if (!$validator) {
                    $this->errors[] = [
                        'line' => $questionNumber,
                        'message' => 'Noma\'lum savol turi: ' . $question['type']
                    ];
                    continue;
                }

                $validation = $validator->validate($question);

                if (!$validation['valid']) {
                    $this->errors[] = [
                        'line' => $questionNumber,
                        'message' => $validation['message']
                    ];
                    continue;
                }

                if ($question['type'] === 'single_choice' && $validator instanceof SingleChoiceValidator) {
                    $validator->validateAnswerFormat($question['answers'], $questionNumber);
                }
            } catch (\Exception $e) {
                $this->errors[] = [
                    'line' => $questionNumber,
                    'message' => $e->getMessage()
                ];
            }
        }
    }

    protected function saveQuestions(array $questions): void
    {
        foreach ($questions as $question) {
            $imageName = !empty($question['image']) ? $question['image'] : 'medicaltest.png';

            $questionText = preg_replace('/<img[^>]*data-image="[^"]*"[^>]*>/i', '', $question['text']);
            $questionText = trim($questionText);

            $baseData = [
                'subject_id' => $this->currentSubjectId,
                'topic_id' => $this->currentTopicId,
                'language_id' => $this->currentLanguageId,
                'question_text' => $questionText,
                'image' => $imageName,
            ];

            switch ($question['type']) {
                case 'matching':
                    $this->saveMatchingQuestion($question, $baseData);
                    break;

                case 'sequence':
                    $this->saveSequenceQuestion($question, $baseData);
                    break;

                default:
                    $this->saveSingleChoiceQuestion($question, $baseData);
                    break;
            }

            $this->uploadedCount++;
        }
    }

    protected function saveSingleChoiceQuestion(array $question, array $baseData): void
    {
        $cleanedAnswers = [];
        foreach ($question['answers'] as $answer) {
            $answerText = preg_replace('/<img[^>]*data-image="[^"]*"[^>]*>/i', '', $answer['text']);
            $answerText = trim($answerText);

            $cleanedAnswers[] = [
                'text' => $answerText,
                'is_correct' => $answer['is_correct'],
            ];
        }

        $baseData['type'] = Question::TYPE_SINGLE_CHOICE;
        $baseData['answers'] = $cleanedAnswers;

        $this->repository->createQuestion($baseData);
    }

    protected function saveMatchingQuestion(array $question, array $baseData): void
    {
        $cleanedAnswers = [];
        foreach ($question['answers'] as $answer) {
            $answerText = preg_replace('/<img[^>]*data-image="[^"]*"[^>]*>/i', '', $answer['text']);
            $answerText = trim($answerText);

            $cleanedAnswers[] = [
                'text' => $answerText,
                'is_correct' => $answer['is_correct'],
            ];
        }

        $baseData['left_items'] = $question['left_items'] ?? [];
        $baseData['right_items'] = $question['right_items'] ?? [];
        $baseData['left_items_title'] = $question['left_items_title'] ?? null;
        $baseData['right_items_title'] = $question['right_items_title'] ?? null;
        $baseData['answers'] = $cleanedAnswers;

        $this->repository->createMatchingQuestion($baseData);
    }

    protected function saveSequenceQuestion(array $question, array $baseData): void
    {
        $cleanedAnswers = [];
        foreach ($question['answers'] as $answer) {
            $answerText = preg_replace('/<img[^>]*data-image="[^"]*"[^>]*>/i', '', $answer['text']);
            $answerText = trim($answerText);

            $cleanedAnswers[] = [
                'text' => $answerText,
                'is_correct' => true,
                'order' => $answer['order'],
            ];
        }

        $baseData['answers'] = $cleanedAnswers;

        $this->repository->createSequenceQuestion($baseData);
    }

    public function parseHtmlContent(string $html): array
    {
        return $this->parseQuestionsFromHtml($html);
    }

    public function extractDocxContent(string $filePath): string
    {
        $customTempDir = storage_path('app/temp');

        if (!file_exists($customTempDir)) {
            mkdir($customTempDir, 0755, true);
        }

        putenv('TMPDIR=' . $customTempDir);
        putenv('TEMP=' . $customTempDir);
        putenv('TMP=' . $customTempDir);

        return $this->documentProcessor->extractContentAsHtml($filePath, $customTempDir);
    }

    public function parseSingleQuestionFromDocx(string $filePath, array $metadata): array
    {
        try {
            $html = $this->extractDocxContent($filePath);
            $questions = $this->parseHtmlContent($html);

            if (count($questions) !== 1) {
                throw new \Exception('File must contain exactly ONE question. Found: ' . count($questions));
            }

            $question = $questions[0];

            if (isset($metadata['type']) && $question['type'] !== $metadata['type']) {
                throw new \Exception("Question type mismatch. Expected: {$metadata['type']}, Found: {$question['type']}");
            }

            if (!isset($question['text']) || empty($question['text'])) {
                throw new \Exception('Question text is empty or missing');
            }

            if (!isset($question['answers']) || empty($question['answers'])) {
                throw new \Exception('No answers found in the question');
            }

            if ($question['type'] === 'matching') {
                if (!isset($question['left_items']) || empty($question['left_items'])) {
                    throw new \Exception('Matching question missing left items');
                }
                if (!isset($question['right_items']) || empty($question['right_items'])) {
                    throw new \Exception('Matching question missing right items');
                }
            }

            if ($question['type'] === 'sequence') {
                foreach ($question['answers'] as $answer) {
                    if (!isset($answer['order'])) {
                        throw new \Exception('Sequence answer missing order field');
                    }
                }
            }

            return $question;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function cleanLatexFromText(string $text): string
    {
        if (substr_count($text, '<math') > 0 || substr_count($text, 'annotation') > 0) {
            $text = preg_replace('/<annotation[^>]*>.*?<\/annotation>/s', '', $text);
            $text = preg_replace('/<semantics>(.*?)<\/semantics>/s', '$1', $text);
            $text = preg_replace('/\\\\\(.*?\\\\\)/s', '', $text);
            $text = preg_replace('/\\\\\[.*?\\\\\]/s', '', $text);
            $text = preg_replace('/\\\\[a-zA-Z]+[\\_\\^]?\{[^}]*\}/s', '', $text);
            $text = preg_replace('/\\\\[a-zA-Z]+(?![a-zA-Z])/s', '', $text);
            $text = preg_replace('/<mrow>|<\/mrow>/i', '', $text);
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);
        }

        return $text;
    }
}
