<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FeeHead extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_static' => 'boolean',
        'is_active' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name, '_');
            }
        });
    }

    /** Non-static (package-eligible) heads only */
    public function scopePackageEligible($query)
    {
        return $query->where('is_static', false)->where('is_active', true);
    }
}
