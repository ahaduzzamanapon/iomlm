<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Survey extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_active'                => 'boolean',
        'allow_multiple_responses' => 'boolean',
    ];

    public function fields()
    {
        return $this->hasMany(SurveyField::class)->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        if (empty($base)) {
            $base = 'survey-' . Str::random(6);
        }
        $slug = $base;
        $count = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $count++;
        }
        return $slug;
    }
}
