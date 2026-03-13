<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentUpload extends Model
{
    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
        'status',
        'total_rows',
        'processed_rows',
        'uploaded_count',
        'skipped_count',
        'error_count',
        'errors',
        'created_faculties',
        'created_groups',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'created_faculties' => 'array',
        'created_groups' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->total_rows === 0) {
            return 0;
        }
        return (int) round(($this->processed_rows / $this->total_rows) * 100);
    }
}
