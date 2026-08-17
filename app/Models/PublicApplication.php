<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicApplication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_no', 'course_id', 'academic_session_id',
        'applicant_name', 'phone', 'date_of_birth', 'occupation', 'education_qualification',
        'ssc_school', 'ssc_board', 'ssc_year',
        'hsc_college', 'hsc_board', 'hsc_year',
        'university_name', 'department_name', 'device_type',
        'gender', 'blood_group_id', 'email', 'national_id', 'passport_no', 'birth_certificate_no',
        'nationality', 'religion_id',
        'present_house', 'present_post_office', 'present_police_station', 'present_district_id', 'present_division_id',
        'same_as_present',
        'permanent_house', 'permanent_post_office', 'permanent_police_station', 'permanent_district_id', 'permanent_division_id',
        'status', 'admin_notes', 'ip_address',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'same_as_present' => 'boolean',
    ];

    public function course()       { return $this->belongsTo(Course::class); }
    public function session()      { return $this->belongsTo(AcademicSession::class, 'academic_session_id'); }
    public function bloodGroup()   { return $this->belongsTo(BloodGroup::class); }
    public function religion()     { return $this->belongsTo(Religion::class); }
    public function presentDistrict()   { return $this->belongsTo(District::class, 'present_district_id'); }
    public function presentDivision()   { return $this->belongsTo(Division::class, 'present_division_id'); }
    public function permanentDistrict() { return $this->belongsTo(District::class, 'permanent_district_id'); }
    public function permanentDivision() { return $this->belongsTo(Division::class, 'permanent_division_id'); }

    public static function generateApplicationNo(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)->max('id') ?? 0;
        return 'APP-' . $year . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'APPROVED' => 'badge-active',
            'REJECTED' => 'badge-cancelled',
            'REVIEWED' => 'badge-scheduled',
            default    => 'badge-secondary',
        };
    }
}
