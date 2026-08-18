<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'options'   => 'array',
        'is_active' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function isMcq(): bool
    {
        return $this->question_type === 'MCQ';
    }

    public function isWritten(): bool
    {
        return $this->question_type === 'WRITTEN';
    }
}
