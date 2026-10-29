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
        'is_expatriate'    => 'boolean',
        'is_international' => 'boolean',
        'reviewed_at'      => 'datetime',
        'discount_percent' => 'float',
        'discount_amount'  => 'float',
        'manual_paid_amount' => 'float',
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

    public function waiverApplication()
    {
        return $this->hasOne(WaiverApplication::class, 'application_no', 'waiver_code');
    }

    /**
     * Get effective discount percentage
     */
    public function getEffectiveDiscountPercentAttribute(): float
    {
        if ((float) $this->discount_percent > 0) {
            return (float) $this->discount_percent;
        }

        if ($this->waiver_code) {
            $wApp = WaiverApplication::where('application_no', $this->waiver_code)->first();
            if ($wApp) {
                if ((float) $wApp->approved_discount_percent > 0) {
                    return (float) $wApp->approved_discount_percent;
                }
                if ($wApp->approved_admission_fee !== null) {
                    $courseFee = (float) ($this->interestedCourse?->admission_fee ?? 0);
                    $batchFee = ($this->batch && (float) $this->batch->admission_fee > 0) ? (float) $this->batch->admission_fee : $courseFee;
                    if ($batchFee > 0 && $batchFee > (float) $wApp->approved_admission_fee) {
                        return round((($batchFee - (float) $wApp->approved_admission_fee) / $batchFee) * 100, 2);
                    }
                }
            }
        }

        if ((float) $this->discount_amount > 0) {
            $courseFee = (float) ($this->interestedCourse?->admission_fee ?? 0);
            $batchFee = ($this->batch && (float) $this->batch->admission_fee > 0) ? (float) $this->batch->admission_fee : $courseFee;
            if ($batchFee > 0) {
                return round(((float) $this->discount_amount / $batchFee) * 100, 2);
            }
        }

        return 0.0;
    }

    /**
     * Get effective discount amount
     */
    public function getEffectiveDiscountAmountAttribute(): float
    {
        if ((float) $this->discount_amount > 0) {
            return (float) $this->discount_amount;
        }

        $courseFee = (float) ($this->interestedCourse?->admission_fee ?? 0);
        $batchFee = ($this->batch && (float) $this->batch->admission_fee > 0) ? (float) $this->batch->admission_fee : $courseFee;

        if ($this->waiver_code) {
            $wApp = WaiverApplication::where('application_no', $this->waiver_code)->first();
            if ($wApp && $wApp->approved_admission_fee !== null && in_array($wApp->apply_for, ['ADMISSION_FEE', 'BOTH'])) {
                if ($batchFee > 0) {
                    $payable = min($batchFee, (float) $wApp->approved_admission_fee);
                    return max(0, $batchFee - $payable);
                }
            }
        }

        $effectivePercent = $this->effective_discount_percent;
        if ($effectivePercent > 0 && $batchFee > 0) {
            return round(($batchFee * $effectivePercent) / 100, 2);
        }

        return 0.0;
    }
}
