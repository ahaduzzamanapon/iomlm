<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyField extends Model
{
    protected $guarded = [];

    protected $casts = [
        'options'     => 'array',
        'is_required' => 'boolean',
        'sort_order'  => 'integer',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function answers()
    {
        return $this->hasMany(SurveyResponseAnswer::class);
    }
}
