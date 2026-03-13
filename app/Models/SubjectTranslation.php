<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectTranslation extends Model
{
    protected $fillable = [
        'subject_id',
        'language_id',
        'name',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
