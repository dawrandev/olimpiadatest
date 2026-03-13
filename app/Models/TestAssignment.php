<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestAssignment extends Model
{
    protected $table = 'test_assignments';

    protected $fillable = [
        'id',
        'parent_assignment_id',
        'is_retake',
        'language_id',
        'faculty_id',
        'group_id',
        'subject_id',
        'duration',
        'question_count',
        'start_time',
        'end_time',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_retake' => 'boolean',
        'is_active' => 'boolean',
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }

    public function parentAssignment()
    {
        return $this->belongsTo(TestAssignment::class, 'parent_assignment_id');
    }

    public function retakes()
    {
        return $this->hasMany(TestAssignment::class, 'parent_assignment_id');
    }

    public function scopeOnlyOriginal($query)
    {
        return $query->where('is_retake', false)
            ->orWhereNull('parent_assignment_id');
    }

    public function scopeOnlyRetakes($query)
    {
        return $query->where('is_retake', true);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_test_assignments')
            ->withTimestamps();
    }

    public function testResults()
    {
        return $this->hasMany(TestResult::class);
    }
}
