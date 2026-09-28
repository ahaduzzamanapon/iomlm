<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionForm extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'same_as_present'  => 'boolean',
        'reviewed_at'      => 'datetime',
        'discount_percent' => 'float',
        'discount_amount'  => 'float',
    ];

    // ── Relationships ──────────────────────────────────────────────────
    public function student()        { return $this->belongsTo(Student::class); }
    public function interestedCourse(){ return $this->belongsTo(Course::class, 'interested_course_id'); }
    public function batch()           { return $this->belongsTo(Batch::class); }
    public function session()        { return $this->belongsTo(AcademicSession::class, 'academic_session_id'); }
    public function reviewer()       { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function bloodGroup()     { return $this->belongsTo(BloodGroup::class); }
    public function religion()       { return $this->belongsTo(Religion::class); }
    public function presentDistrict()    { return $this->belongsTo(District::class, 'present_district_id'); }
    public function presentDivision()    { return $this->belongsTo(Division::class, 'present_division_id'); }
    public function permanentDistrict()  { return $this->belongsTo(District::class, 'permanent_district_id'); }
    public function permanentDivision()  { return $this->belongsTo(Division::class, 'permanent_division_id'); }

    // ── Helpers ────────────────────────────────────────────────────────
    public static function generateApplicationNo(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)->max('id') ?? 0;
        return 'APP-' . $year . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getIsPublicAttribute(): bool
    {
        return $this->source === 'PUBLIC';
    }
}
