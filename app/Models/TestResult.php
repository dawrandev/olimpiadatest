<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    protected $fillable = [
        'test_assignment_id',
        'student_id',
        'score',
        'grade',
        'total_questions',
        'correct_answers',
        'started_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function testAssignment()
    {
        return $this->belongsTo(TestAssignment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class);
    }

    public function results()
    {
        return $this->hasMany(StudentAnswer::class);
    }
}
