<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacultyTranslation extends Model
{
    protected $fillable =
    [
        'faculty_id',
        'language_id',
        'name'
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
