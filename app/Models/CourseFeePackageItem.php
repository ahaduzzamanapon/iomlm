<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseFeePackageItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity'        => 'integer',
        'amount_per_unit' => 'float',
        'total_amount'    => 'float',
    ];

    public function package()
    {
        return $this->belongsTo(CourseFeePackage::class, 'package_id');
    }

    public function feeHead()
    {
        return $this->belongsTo(FeeHead::class, 'fee_head_id');
    }
}
