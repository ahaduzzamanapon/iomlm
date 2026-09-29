<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $guarded = [];

    protected $casts = [
        'admission_fee'             => 'float',
        'readmission_fee'           => 'float',
        'is_poor_fund_applicable'   => 'boolean',
        'is_active'                 => 'boolean',
    ];

    public function readmissions()
    {
        return $this->hasMany(Readmission::class, 'course_id');
    }

    public function courseTransfersFrom()
    {
        return $this->hasMany(CourseTransfer::class, 'from_course_id');
    }

    public function courseTransfersTo()
    {
        return $this->hasMany(CourseTransfer::class, 'to_course_id');
    }

    public function scopePoorFundApplicable($query)
    {
        return $query->where('is_poor_fund_applicable', true);
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class, 'course_id')->orderBy('sequence_no');
    }

    public function courseSubjectMaps()
    {
        return $this->hasMany(CourseSubjectMap::class, 'course_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'course_subject_maps', 'course_id', 'subject_id')
            ->withPivot('semester_id', 'sort_order');
    }

    public function batches()
    {
        return $this->hasMany(Batch::class, 'course_id');
    }

    public function feePackages()
    {
        return $this->hasMany(CourseFeePackage::class, 'course_id')->where('is_active', true)->orderBy('id');
    }

    public function defaultPackage()
    {
        return $this->hasOne(CourseFeePackage::class, 'course_id')->where('is_default', true);
    }

    public function getFormattedCodeAttribute(): string
    {
        if (!empty($this->code)) {
            $digits = preg_replace('/\D/', '', $this->code);
            if (!empty($digits)) {
                return str_pad(substr($digits, 0, 2), 2, '0', STR_PAD_LEFT);
            }
        }
        return str_pad(($this->id % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function defaultDepartments(): array
    {
        return [
            'BA in Dawah and Islamic Studies',
            'School Maktab',
            'Hifz Course',
            'Nazera Course',
            'Single Course',
            'Farz E Ain Course',
            'Dawrah Hadith',
        ];
    }

    public static function monthsList(): array
    {
        return [
            'January'   => 'জানুয়ারি (January)',
            'February'  => 'ফেব্রুয়ারি (February)',
            'March'     => 'মার্চ (March)',
            'April'     => 'এপ্রিল (April)',
            'May'       => 'মে (May)',
            'June'      => 'জুন (June)',
            'July'      => 'জুলাই (July)',
            'August'    => 'আগস্ট (August)',
            'September' => 'সেপ্টেম্বর (September)',
            'October'   => 'অক্টোবর (October)',
            'November'  => 'নভেম্বর (November)',
            'December'  => 'ডিসেম্বর (December)',
        ];
    }

    public function getDurationCycleTextAttribute(): string
    {
        $months = self::monthsList();
        $start = $this->start_month ? ($months[$this->start_month] ?? $this->start_month) : null;
        $end   = $this->end_month ? ($months[$this->end_month] ?? $this->end_month) : null;

        if ($start && $end) {
            return "{$start} হতে {$end}";
        }
        if ($start) {
            return "{$start} থেকে শুরু";
        }
        return "{$this->duration_value} " . ($this->duration_unit === 'YEAR' ? 'বছর' : 'মাস');
    }

    public function getFeeCycleTextAttribute(): string
    {
        $months = self::monthsList();
        $start = $this->fee_start_month ? ($months[$this->fee_start_month] ?? $this->fee_start_month) : null;
        $end   = $this->fee_end_month ? ($months[$this->fee_end_month] ?? $this->fee_end_month) : null;

        if ($start && $end) {
            return "{$start} হতে {$end}";
        }
        if ($start) {
            return "{$start} হতে চলমান";
        }
        return "প্রতি মাস";
    }
}
