<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'language_id',
        'subject_id',
        'topic_id',
        'type',
        'text',
        'left_items_title',
        'right_items_title',
        'image'
    ];

    const TYPE_SINGLE_CHOICE = 'single_choice';
    const TYPE_MATCHING = 'matching';
    const TYPE_SEQUENCE = 'sequence';

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class);
    }

    public function matchingPairs()
    {
        return $this->hasMany(QuestionMatchingPair::class);
    }

    public function scopeSingleChoice($query)
    {
        return $query->where('type', self::TYPE_SINGLE_CHOICE);
    }

    public function scopeMatching($query)
    {
        return $query->where('type', self::TYPE_MATCHING);
    }

    public function scopeSequence($query)
    {
        return $query->where('type', self::TYPE_SEQUENCE);
    }

    public function isSingleChoice(): bool
    {
        return $this->type === self::TYPE_SINGLE_CHOICE;
    }

    public function isMatching(): bool
    {
        return $this->type === self::TYPE_MATCHING;
    }

    public function isSequence(): bool
    {
        return $this->type === self::TYPE_SEQUENCE;
    }
}
