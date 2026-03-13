<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionMatchingPair extends Model
{
    use HasFactory;

    protected $table = 'question_matching_pairs';

    protected $fillable = [
        'question_id',
        'side',
        'key',
        'text',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function scopeLeftSide($query)
    {
        return $query->where('side', 'left')->orderBy('order');
    }

    public function scopeRightSide($query)
    {
        return $query->where('side', 'right')->orderBy('order');
    }

    public function scopeForQuestion($query, int $questionId)
    {
        return $query->where('question_id', $questionId);
    }

    public function isLeftSide(): bool
    {
        return $this->side === 'left';
    }

    public function isRightSide(): bool
    {
        return $this->side === 'right';
    }

    public function getFormattedTextAttribute(): string
    {
        return $this->key . '. ' . $this->text;
    }
}
