<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'question_id',
        'language_id',
        'text',
        'is_correct',
        'order'
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'order' => 'integer',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class);
    }

    public function scopeInCorrectOrder($query)
    {
        return $query->orderBy('order');
    }

    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    public static function getSequenceOrder($questionId)
    {
        return self::where('question_id', $questionId)
            ->orderBy('order')
            ->pluck('id')
            ->toArray();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($answer) {
            if (empty($answer->language_id) && !empty($answer->question_id)) {
                $question = \App\Models\Question::find($answer->question_id);
                if ($question) {
                    $answer->language_id = $question->language_id;
                }
            }
        });
    }
}
