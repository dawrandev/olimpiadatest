<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'user_id',
        'group_id',
        'student_id',
        'full_name',
        'passport',
        'jshshir',
        'course',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function faculty()
    {
        return $this->group->faculty();
    }

    public function testAssignments()
    {
        return $this->hasManyThrough(TestAssignment::class, Group::class, 'id', 'group_id', 'group_id', 'id');
    }
}
