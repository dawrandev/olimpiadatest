<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicTranslation extends Model
{
    protected $fillable = [
        'topic_id',
        'language_id',
        'name',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
