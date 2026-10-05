<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CourseCoupon extends Model
{
    protected $table = 'course_coupons';

    protected $guarded = [];

    protected $casts = [
        'starts_at'       => 'date',
        'ends_at'         => 'date',
        'is_active'       => 'boolean',
        'discount_amount' => 'float',
        'max_uses'        => 'integer',
        'used_count'      => 'integer',
    ];

    /**
     * Relationship to Course
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Validate whether this coupon is valid for a given course
     *
     * @param int|null $courseId
     * @return array [ 'valid' => bool, 'message' => string ]
     */
    public function validateForCourse(?int $courseId = null): array
    {
        if (!$this->is_active) {
            return [
                'valid'   => false,
                'message' => '✕ এই কুপন কোডটি ('.$this->code.') বর্তমানে নিষ্ক্রিয় করা রয়েছে।'
            ];
        }

        if ($courseId && $this->course_id !== (int) $courseId) {
            $courseName = $this->course?->name ?? 'অন্য একটি কোর্সের';
            return [
                'valid'   => false,
                'message' => '✕ এই কুপন কোডটি শুধুমাত্র "'.$courseName.'" কোর্সের জন্য প্রযোজ্য।'
            ];
        }

        $today = Carbon::today();

        if ($this->starts_at && $today->lt($this->starts_at)) {
            return [
                'valid'   => false,
                'message' => '✕ এই কুপন কোডটির কার্যকারিতা ' . \Carbon\Carbon::parse($this->starts_at)->format('d M Y') . ' থেকে শুরু হবে।'
            ];
        }

        if ($this->ends_at && $today->gt($this->ends_at)) {
            return [
                'valid'   => false,
                'message' => '✕ এই কুপন কোডের মেয়াদ ' . \Carbon\Carbon::parse($this->ends_at)->format('d M Y') . ' তারিখে শেষ হয়ে গেছে।'
            ];
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return [
                'valid'   => false,
                'message' => '✕ দুঃখিত, এই কুপন কোডের ব্যবহারের সর্বোচ্চ সীমা ('.$this->max_uses.' বার) পূর্ণ হয়ে গেছে।'
            ];
        }

        return [
            'valid'   => true,
            'message' => '✓ কুপন কোডটি বৈধ ও সক্রিয়।'
        ];
    }

    /**
     * Calculate discount amount in BDT for a given base fee
     *
     * @param float $baseFee
     * @return float
     */
    public function calculateDiscount(float $baseFee): float
    {
        if ($baseFee <= 0) {
            return 0.0;
        }

        if ($this->discount_type === 'PERCENT') {
            $percent = max(0, min(100, (float) $this->discount_amount));
            return round(($baseFee * $percent) / 100, 2);
        }

        // FIXED amount
        return min($baseFee, round((float) $this->discount_amount, 2));
    }
}
