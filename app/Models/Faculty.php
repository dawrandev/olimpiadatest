<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = [
        'id',
        'name',
    ];

    public function translations()
    {
        return $this->hasMany(FacultyTranslation::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function testAssignments()
    {
        return $this->hasMany(TestAssignment::class);
    }

    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            Group::class,
        );

    }
}
