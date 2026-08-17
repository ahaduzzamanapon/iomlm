<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseFeePackage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function items()
    {
        return $this->hasMany(CourseFeePackageItem::class, 'package_id')->orderBy('sort_order');
    }

    public function getTotalAttribute(): float
    {
        return $this->items->sum('total_amount');
    }
}
