<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'id'
    ];

    public function translations()
    {
        return $this->hasMany(SubjectTranslation::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function testAssignments()
    {
        return $this->hasMany(TestAssignment::class);
    }

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
}
