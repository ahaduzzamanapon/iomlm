<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyResponseAnswer extends Model
{
    protected $guarded = [];

    public function response()
    {
        return $this->belongsTo(SurveyResponse::class, 'survey_response_id');
    }

    public function field()
    {
        return $this->belongsTo(SurveyField::class, 'survey_field_id');
    }

    public function getFormattedAnswerAttribute()
    {
        $val = $this->answer_value;
        if (empty($val)) {
            return '—';
        }

        $decoded = json_decode($val, true);
        if (is_array($decoded)) {
            return implode(', ', $decoded);
        }

        return $val;
    }
}
