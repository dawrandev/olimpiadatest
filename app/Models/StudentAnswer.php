<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'test_result_id',
        'question_id',
        'answer_id',
        'answer_text',
        'is_correct',
        'order',
        'partial_score',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'partial_score' => 'decimal:2',
    ];

    public function testResult()
    {
        return $this->belongsTo(TestResult::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    public function checkMatchingAnswer(): bool
    {
        if (!$this->answer_text || !$this->question) {
            return false;
        }

        $correctAnswer = $this->question->answers()
            ->where('is_correct', true)
            ->first();

        if (!$correctAnswer) {
            return false;
        }

        $userAnswer = $this->normalizeMatchingAnswer($this->answer_text);
        $correctAnswerText = $this->normalizeMatchingAnswer($correctAnswer->text);

        return $userAnswer === $correctAnswerText;
    }

    public function checkSequenceAnswer(): bool
    {
        if (!$this->answer_text || !$this->question) {
            return false;
        }

        $userAnswerIds = array_map('intval', explode(',', $this->answer_text));

        $correctSequence = $this->question->answers()
            ->where('is_correct', true)
            ->orderBy('order')
            ->pluck('id')
            ->toArray();

        return $userAnswerIds === $correctSequence;
    }
    private function normalizeMatchingAnswer(string $text): string
    {
        return str_replace([' ', ';', ','], ['', ',', ','], trim($text, ',; '));
    }
}
