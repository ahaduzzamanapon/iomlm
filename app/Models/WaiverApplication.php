<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaiverApplication extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_abroad'              => 'boolean',
        'same_as_present'        => 'boolean',
        'is_present_iom_student' => 'boolean',
        'is_married'             => 'boolean',
        'is_used'                => 'boolean',
        'reviewed_at'            => 'datetime',
        'date_of_birth'          => 'date',
        'approved_admission_fee' => 'float',
        'approved_discount_value'=> 'float',
    ];

    public function division()        { return $this->belongsTo(Division::class); }
    public function reviewer()        { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function admissionForm()   { return $this->belongsTo(AdmissionForm::class); }
    public function approvedPackage() { return $this->belongsTo(CourseFeePackage::class, 'approved_package_id'); }
    public function course()          { return $this->belongsTo(Course::class); }

    public static function generateApplicationNo(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)->max('id') ?? 0;
        return 'POOR-' . $year . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}
