<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'name',
        'code'
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function testSessions()
    {
        return $this->hasMany(TestSession::class);
    }

    public function faculties()
    {
        return $this->hasMany(FacultyTranslation::class);
    }

    public function testAssignments()
    {
        return $this->hasMany(TestAssignment::class);
    }
}
