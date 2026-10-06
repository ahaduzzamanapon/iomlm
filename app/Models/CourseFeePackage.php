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

    public function getMonthlyFeeAttribute(): float
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->with('feeHead')->get();
        $tuitionItem = $items->first(function ($it) {
            return ($it->feeHead && $it->feeHead->slug === 'tuition_fee')
                || str_contains(mb_strtolower($it->label ?? ''), 'tuition')
                || str_contains(mb_strtolower($it->feeHead?->name ?? ''), 'tuition')
                || str_contains(mb_strtolower($it->feeHead?->name ?? ''), 'মাসিক')
                || str_contains(mb_strtolower($it->label ?? ''), 'মাসিক');
        });

        if ($tuitionItem) {
            if ($tuitionItem->amount_per_unit > 0) {
                return (float) $tuitionItem->amount_per_unit;
            }
            if ($tuitionItem->quantity > 0 && $tuitionItem->total_amount > 0) {
                return round((float) $tuitionItem->total_amount / $tuitionItem->quantity, 2);
            }
        }

        if (preg_match('/\b(\d{2,5})\b/', $this->name, $matches)) {
            return (float) $matches[1];
        }

        return 0.0;
    }
}
