<?php

namespace App\Repositories\Admin;

use App\Models\Question;
use App\Models\Answer;
use App\Models\Language;
use App\Models\QuestionMatchingPair;
use Illuminate\Support\Facades\DB;

class QuestionRepository
{
    /**
     * Create single choice question (existing method)
     */
    public function createQuestion(array $data): Question
    {
        $question = Question::create([
            'subject_id' => $data['subject_id'],
            'topic_id' => $data['topic_id'],
            'language_id' => $data['language_id'],
            'type' => $data['type'],
            'text' => $data['question_text'],
            'image' => $data['image'] ?? 'medicaltest.png',
        ]);

        if (!empty($data['answers'])) {
            foreach ($data['answers'] as $answerData) {
                Answer::create([
                    'language_id' => $question->language_id,
                    'question_id' => $question->id,
                    'text' => $answerData['text'],
                    'is_correct' => $answerData['is_correct'] ?? false,
                    'order' => $answerData['order'] ?? 0,
                ]);
            }
        }

        return $question;
    }

    /**
     * Create matching question with left/right pairs
     */
    public function createMatchingQuestion(array $data): Question
    {
        DB::beginTransaction();

        try {
            $question = Question::create([
                'subject_id'  => $data['subject_id'],
                'topic_id'    => $data['topic_id'],
                'language_id' => $data['language_id'],
                'type'        => Question::TYPE_MATCHING,
                'text'        => $data['question_text'],
                'image'       => $data['image'] ?? null,
                'left_items_title'  => $data['left_items_title'] ?? null,   // ✅ Yangi
                'right_items_title' => $data['right_items_title'] ?? null,  // ✅ Yangi
            ]);

            // Left items
            if (!empty($data['left_items'])) {
                foreach ($data['left_items'] as $order => $item) {
                    QuestionMatchingPair::create([
                        'question_id' => $question->id,
                        'side'        => 'left',
                        'key'         => $item['key'],
                        'text'        => $item['text'],
                        'order'       => $order,
                    ]);
                }
            }

            // Right items
            if (!empty($data['right_items'])) {
                foreach ($data['right_items'] as $order => $item) {
                    QuestionMatchingPair::create([
                        'question_id' => $question->id,
                        'side'        => 'right',
                        'key'         => $item['key'],
                        'text'        => $item['text'],
                        'order'       => $order,
                    ]);
                }
            }

            // Answer variants
            if (!empty($data['answers'])) {
                foreach ($data['answers'] as $answerData) {
                    $question->answers()->create([
                        'language_id' => $data['language_id'],
                        'text'        => $answerData['text'],
                        'is_correct'  => $answerData['is_correct'],
                    ]);
                }
            }

            DB::commit();

            return $question->load(['answers', 'matchingPairs']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Get question by ID with all relations
     */
    public function findWithRelations(int $id): ?Question
    {
        return Question::with([
            'answers',
            'subject',
            'topic',
            'language',
            'matchingPairs'
        ])->find($id);
    }

    /**
     * Delete question (with cleanup)
     */
    public function delete(int $id): bool
    {
        $question = Question::find($id);

        if (!$question) {
            return false;
        }

        // Delete image if exists
        if ($question->image && $question->image !== 'medicaltest.png') {
            $imagePath = public_path('storage/questions/' . $question->image);
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        // Cascade delete will handle answers and matching pairs
        return $question->delete();
    }

    /**
     * Get questions with pagination and filters
     */
    public function getPaginated($perPage = 20, $filters = [])
    {
        $query = Question::query()
            ->with(['answers', 'subject', 'topic', 'language', 'matchingPairs']);

        if (!empty($filters['language_id'])) {
            $query->where('language_id', $filters['language_id']);
        }

        if (!empty($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (!empty($filters['topic_id'])) {
            $query->where('topic_id', $filters['topic_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('text', 'LIKE', "%{$search}%");
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get all languages
     */
    public function getAllLanguages()
    {
        return Language::all();
    }

    /**
     * Get question statistics by type
     */
    public function getStatsByType(array $filters = []): array
    {
        $query = Question::query();

        if (!empty($filters['language_id'])) {
            $query->where('language_id', $filters['language_id']);
        }

        if (!empty($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (!empty($filters['topic_id'])) {
            $query->where('topic_id', $filters['topic_id']);
        }

        $stats = $query->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        return [
            'single_choice' => $stats[Question::TYPE_SINGLE_CHOICE] ?? 0,
            'matching' => $stats[Question::TYPE_MATCHING] ?? 0,
            'sequence' => $stats[Question::TYPE_SEQUENCE] ?? 0,
            'total' => array_sum($stats),
        ];
    }

    /**
     * Update question (for future use)
     */
    public function update(int $id, array $data): ?Question
    {
        $question = Question::find($id);

        if (!$question) {
            return null;
        }

        DB::beginTransaction();

        try {
            // Update main question data
            $question->update([
                'text' => $data['question_text'] ?? $question->text,
                'image' => $data['image'] ?? $question->image,
            ]);

            // Update answers if provided
            if (!empty($data['answers'])) {
                // Delete old answers
                $question->answers()->delete();

                // Create new answers
                foreach ($data['answers'] as $answerData) {
                    $question->answers()->create([
                        'language_id' => $question->language_id,
                        'text'        => $answerData['text'],
                        'is_correct'  => $answerData['is_correct'],
                    ]);
                }
            }

            // Update matching pairs if it's a matching question
            if ($question->isMatching() && !empty($data['left_items']) && !empty($data['right_items'])) {
                // Delete old pairs
                $question->matchingPairs()->delete();

                // Create new left pairs
                foreach ($data['left_items'] as $order => $item) {
                    QuestionMatchingPair::create([
                        'question_id' => $question->id,
                        'side'        => 'left',
                        'key'         => $item['key'],
                        'text'        => $item['text'],
                        'order'       => $order,
                    ]);
                }

                // Create new right pairs
                foreach ($data['right_items'] as $order => $item) {
                    QuestionMatchingPair::create([
                        'question_id' => $question->id,
                        'side'        => 'right',
                        'key'         => $item['key'],
                        'text'        => $item['text'],
                        'order'       => $order,
                    ]);
                }
            }

            DB::commit();

            return $question->fresh(['answers', 'matchingPairs']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get matching pairs for a question
     */
    public function getMatchingPairs(int $questionId): array
    {
        $question = Question::with('matchingPairs')->find($questionId);

        if (!$question || !$question->isMatching()) {
            return ['left' => [], 'right' => []];
        }

        $left = $question->matchingPairs()
            ->where('side', 'left')
            ->orderBy('order')
            ->get()
            ->toArray();

        $right = $question->matchingPairs()
            ->where('side', 'right')
            ->orderBy('order')
            ->get()
            ->toArray();

        return [
            'left' => $left,
            'right' => $right,
        ];
    }

    /**
     * Bulk delete questions
     */
    public function bulkDelete(array $ids): int
    {
        $questions = Question::whereIn('id', $ids)->get();

        $deletedCount = 0;

        foreach ($questions as $question) {
            if ($this->delete($question->id)) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    public function createSequenceQuestion(array $data): Question
    {
        $question = Question::create([
            'subject_id' => $data['subject_id'],
            'topic_id' => $data['topic_id'],
            'language_id' => $data['language_id'],
            'type' => Question::TYPE_SEQUENCE,
            'text' => $data['question_text'],
            'image' => $data['image'] ?? 'medicaltest.png',
        ]);

        // Javoblarni order bilan saqlash
        if (!empty($data['answers'])) {
            foreach ($data['answers'] as $answerData) {
                Answer::create([
                    'language_id' => $question->language_id,
                    'question_id' => $question->id,
                    'text' => $answerData['text'],
                    'is_correct' => true, // Barcha variantlar to'g'ri
                    'order' => $answerData['order'], // To'g'ri ketma-ketlik
                ]);
            }
        }

        return $question;
    }

    protected function formatMatchingItems(array $leftItems, array $rightItems): string
    {
        $formatted = "Chap tomon:\n";
        foreach ($leftItems as $item) {
            $formatted .= "{$item['key']}. {$item['text']}\n";
        }

        $formatted .= "\nO'ng tomon:\n";
        foreach ($rightItems as $item) {
            $formatted .= "{$item['key']}. {$item['text']}\n";
        }

        return $formatted;
    }

    public function findById(int $id): ?Question
    {
        return Question::with('answers')->find($id);
    }

    public function getAll(array $filters = [])
    {
        $query = Question::with(['subject', 'topic', 'language', 'answers']);

        if (!empty($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (!empty($filters['topic_id'])) {
            $query->where('topic_id', $filters['topic_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->paginate(20);
    }
}
