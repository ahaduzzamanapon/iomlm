<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date_of_birth'     => 'date',
        'has_course_access' => 'boolean',
        'is_common_account' => 'boolean',
        'monthly_discount'  => 'float',
        'fee_package_id'    => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($student) {
            if (empty($student->temporary_password)) {
                $student->temporary_password = self::generateDefaultPassword();
            }
        });
    }

    /**
     * Generate 5-digit numeric password (১০০০০ - ৯৯৯৯৯)
     */
    public static function generateDefaultPassword(): string
    {
        return (string) random_int(10000, 99999);
    }

    /**
     * Get or generate a valid 5-digit numeric password
     */
    public function getOrGenerateNumericPassword(): string
    {
        if (!empty($this->temporary_password) && is_numeric($this->temporary_password) && strlen((string)$this->temporary_password) === 5) {
            return (string) $this->temporary_password;
        }

        $password = self::generateDefaultPassword();
        $this->temporary_password = $password;
        if ($this->exists) {
            $this->saveQuietly();
        }
        return $password;
    }

    // ── Relationships ───────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function feePackage()
    {
        return $this->belongsTo(\App\Models\CourseFeePackage::class, 'fee_package_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(\App\Models\AuditLog::class, 'auditable_id')
                    ->where('auditable_type', self::class)
                    ->latest();
    }

    public function loginHistories()
    {
        return $this->hasMany(\App\Models\LoginHistory::class, 'student_id')->latest();
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function admissionForms()
    {
        return $this->hasMany(AdmissionForm::class, 'student_id');
    }

    // Alias for show page
    public function admissions()
    {
        return $this->hasMany(AdmissionForm::class, 'student_id')->with('interestedCourse')->latest();
    }

    public function invoices()
    {
        return $this->hasMany(\App\Models\Invoice::class, 'student_id')->latest();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function results()
    {
        return $this->hasMany(Result::class, 'student_id');
    }

    public function finalMarks()
    {
        return $this->hasMany(\App\Models\FinalMark::class, 'student_id');
    }

    public function documents()
    {
        return $this->hasMany(StudentDocument::class, 'student_id');
    }

    public function readmissions()
    {
        return $this->hasMany(\App\Models\Readmission::class, 'student_id')->latest();
    }

    public function courseTransfers()
    {
        return $this->hasMany(\App\Models\CourseTransfer::class, 'student_id')->latest();
    }

    // ── Student Profile Management Helpers ───────────────────────────────
    public function toggleCourseAccess(?bool $status = null, ?string $reason = null): bool
    {
        $oldStatus = (bool) ($this->has_course_access ?? true);
        $newStatus = $status !== null ? $status : !$oldStatus;

        $this->has_course_access = $newStatus;
        $this->save();

        \App\Models\AuditLog::log(
            'course_access_toggled',
            $this,
            ['has_course_access' => $oldStatus],
            ['has_course_access' => $newStatus],
            $reason ?: ($newStatus ? 'কোর্স অ্যাক্সেস চালু করা হয়েছে' : 'কোর্স অ্যাক্সেস বন্ধ করা হয়েছে')
        );

        return $newStatus;
    }

    public function cancelAdmission(?string $reason = null): void
    {
        $oldStatus = $this->status;
        $this->status = 'CANCELLED';
        $this->has_course_access = false;
        $this->save();

        // Update active enrollments
        $this->enrollments()->whereIn('status', ['ACTIVE', 'ENROLLED', 'PENDING'])->update([
            'status' => 'CANCELLED'
        ]);

        \App\Models\AuditLog::log(
            'admission_cancelled',
            $this,
            ['status' => $oldStatus, 'has_course_access' => true],
            ['status' => 'CANCELLED', 'has_course_access' => false],
            $reason ?: 'ভর্তি বাতিল করা হয়েছে'
        );
    }

    public function adjustFeeStructure(?int $feePackageId, float $discount = 0, string $discountType = 'FIXED', ?string $remarks = null): void
    {
        $oldValues = [
            'fee_package_id'   => $this->fee_package_id,
            'monthly_discount' => (float)$this->monthly_discount,
            'discount_type'    => $this->discount_type,
            'poor_fund_remarks'=> $this->poor_fund_remarks,
        ];

        $this->fee_package_id    = $feePackageId;
        $this->monthly_discount  = $discount;
        $this->discount_type     = $discountType;
        $this->poor_fund_remarks = $remarks;
        $this->save();

        $newValues = [
            'fee_package_id'   => $feePackageId,
            'monthly_discount' => $discount,
            'discount_type'    => $discountType,
            'poor_fund_remarks'=> $remarks,
        ];

        \App\Models\AuditLog::log(
            'fee_structure_adjusted',
            $this,
            $oldValues,
            $newValues,
            'পুওর ফান্ড ও ফি কাঠামো সমন্বয় করা হয়েছে'
        );
    }

    // ── Profile Completion Logic ──────────────────────────────────────────
    public static function profileFieldsDefinition(): array
    {
        return [
            'name'                    => 'পূর্ণ নাম',
            'phone'                   => 'মোবাইল নম্বর',
            'email'                   => 'ইমেইল ঠিকানা',
            'gender'                  => 'লিঙ্গ',
            'date_of_birth'           => 'জন্ম তারিখ',
            'blood_group'             => 'রক্তের গ্রুপ',
            'national_id'             => 'জাতীয় পরিচয়পত্র / জন্ম নিবন্ধন',
            'father_name'             => 'পিতার নাম',
            'mother_name'             => 'মাতার নাম',
            'guardian_name'           => 'অভিভাবকের নাম',
            'guardian_phone'          => 'অভিভাবকের মোবাইল',
            'guardian_relation'       => 'অভিভাবকের সাথে সম্পর্ক',
            'address'                 => 'বর্তমান ঠিকানা',
            'permanent_address'       => 'স্থায়ী ঠিকানা',
            'occupation'              => 'বর্তমান পেশা',
            'education_qualification' => 'শিক্ষাগত যোগ্যতা',
            'ssc_board'               => 'এসএসসি / দাখিল বোর্ড',
            'ssc_year'                => 'এসএসসি পাসের সন',
            'nationality'             => 'জাতীয়তা',
            'photo_url'               => 'প্রোফাইল ছবি',
        ];
    }

    public function calculateProfileCompletion(): int
    {
        $fields = self::profileFieldsDefinition();
        $totalFields = count($fields);
        $completedCount = 0;

        foreach (array_keys($fields) as $field) {
            $val = trim((string) ($this->{$field} ?? ''));
            if ($val !== '') {
                $completedCount++;
            }
        }

        $percent = (int) round(($completedCount / $totalFields) * 100);
        $this->profile_completed_percent = $percent;
        $this->saveQuietly();

        return $percent;
    }

    /**
     * Resolve default avatar image by gender
     */
    public static function defaultAvatarForGender(?string $gender): string
    {
        $g = strtolower($gender ?? '');
        if (str_contains($g, 'female') || str_contains($g, 'মহিলা') || str_contains($g, 'বোন')) {
            return '/images/avatars/female_avatar_1.jpg';
        }
        if (str_contains($g, 'male') || str_contains($g, 'পুরুষ') || str_contains($g, 'ভাই')) {
            return '/images/avatars/male_avatar_1.png';
        }
        return '/images/avatars/female_avatar_1.jpg';
    }

    /**
     * Complete list of predefined gender-based Islamic avatar options
     */
    public static function availableAvatars(): array
    {
        return [
            'Female' => [
                ['path' => '/images/avatars/female_avatar_1.jpg', 'title' => 'মার্জিত হিজাব (Hijab)',   'is_default' => true],
                ['path' => '/images/avatars/female_avatar_2.jpg', 'title' => 'গোলাপী হিজাব (Pink Hijab)', 'is_default' => false],
                ['path' => '/images/avatars/female_avatar_3.jpg', 'title' => 'লাল হিজাব (Red Hijab)',     'is_default' => false],
            ],
            'Male' => [
                ['path' => '/images/avatars/male_avatar_1.png', 'title' => 'সাদা টুপি (White Topi)',   'is_default' => true],
                ['path' => '/images/avatars/male_avatar_2.jpg', 'title' => 'নকশা টুপি (Pattern Topi)', 'is_default' => false],
            ],
        ];
    }

    /**
     * Get student photo URL with gender default avatar fallback
     */
    public function getPhotoUrlAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        return self::defaultAvatarForGender($this->gender);
    }

    /**
     * Resolve 2-digit Academic Year code from batch, active session or date (Digits 1-2)
     */
    public static function resolveAcademicYearCode(?Batch $batch = null): string
    {
        if ($batch) {
            $ay = $batch->academicYear ?: ($batch->academic_year_id ? AcademicYear::find($batch->academic_year_id) : null);
            if ($ay) {
                if (!empty($ay->start_date)) {
                    return date('y', strtotime($ay->start_date));
                }
                if (preg_match('/\b(20\d{2})\b/', $ay->name, $ym)) {
                    return substr($ym[1], -2);
                }
            }
            if (!empty($batch->start_date)) {
                return date('y', strtotime($batch->start_date));
            }
        }
        $activeAy = AcademicYear::where('is_active', 1)->first();
        if ($activeAy) {
            if (!empty($activeAy->start_date)) {
                return date('y', strtotime($activeAy->start_date));
            }
            if (preg_match('/\b(20\d{2})\b/', $activeAy->name, $ym)) {
                return substr($ym[1], -2);
            }
        }
        return date('y');
    }

    /**
     * Resolve 2-digit Batch Number code from batch name or code (Digits 3-4)
     */
    public static function resolveBatchNumberCode(?Batch $batch = null): string
    {
        if (!$batch) {
            return '01';
        }

        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $name = str_replace($bn, $en, trim((string)($batch->name ?? '')));
        $code = str_replace($bn, $en, trim((string)($batch->batch_code ?? '')));

        // 1. If name is purely numeric (e.g. "01", "1", "12")
        if (is_numeric($name)) {
            return str_pad(((int)$name) % 100, 2, '0', STR_PAD_LEFT);
        }

        // 2. If name contains numbers (e.g. "Batch 01", "নাজেরা-০১", "Alim 2717")
        if (preg_match_all('/\d+/', $name, $matches)) {
            $numbers = $matches[0];
            foreach ($numbers as $numStr) {
                if (strlen($numStr) === 4) {
                    if (in_array(substr($numStr, 0, 2), ['20'])) {
                        continue; // Skip calendar years like 2026
                    }
                    if (in_array(substr($numStr, 0, 2), ['25', '26', '27', '28', '29', '30'])) {
                        return substr($numStr, 2, 2);
                    }
                }
                return str_pad(((int)$numStr) % 100, 2, '0', STR_PAD_LEFT);
            }
        }

        // 3. Fallback to batch_code (e.g. "ALI-2026-11" -> 11, or "16" -> 16)
        if (!empty($code)) {
            if (str_contains($code, '-')) {
                $parts = explode('-', $code);
                $lastPart = end($parts);
                if (is_numeric($lastPart)) {
                    return str_pad(((int)$lastPart) % 100, 2, '0', STR_PAD_LEFT);
                }
            }
            if (is_numeric($code)) {
                return str_pad(((int)$code) % 100, 2, '0', STR_PAD_LEFT);
            }
            if (preg_match('/\d+$/', $code, $m)) {
                return str_pad(((int)$m[0]) % 100, 2, '0', STR_PAD_LEFT);
            }
        }

        return str_pad(($batch->id % 100), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Resolve 2-digit Course Code from Course model (Digits 5-6)
     */
    public static function resolveCourseCode(?Course $course = null, ?int $courseId = null): string
    {
        if (!$course && $courseId) {
            $course = Course::find($courseId);
        }
        if ($course) {
            if (!empty($course->code)) {
                $digits = preg_replace('/\D/', '', $course->code);
                if (!empty($digits)) {
                    return str_pad(substr($digits, 0, 2), 2, '0', STR_PAD_LEFT);
                }
            }
            return str_pad(($course->id % 100), 2, '0', STR_PAD_LEFT);
        }
        return '01';
    }

    /**
     * Resolve 1-digit Gender Code (Digit 7: 1 = Male, 2 = Female)
     */
    public static function resolveGenderCode(?string $gender = null): string
    {
        if (!empty($gender)) {
            $g = strtolower(trim($gender));
            if (
                in_array($g, ['female', '2', 'f', 'নারি', 'নারী', 'মহিলা', 'বোন', 'মেয়ে', 'মেয়ে'])
                || str_contains($g, 'female')
                || str_contains($g, 'মহিলা')
                || str_contains($g, 'নারী')
                || str_contains($g, 'বোন')
            ) {
                return '2';
            }
        }
        return '1';
    }

    /**
     * Generate standard 11-digit Student ID: YYBBCCGRRRR
     * - Digits 1-2: Academic Year (e.g. 27, 28)
     * - Digits 3-4: Batch Number (e.g. 01)
     * - Digits 5-6: Course Code (e.g. 01)
     * - Digit 7: Gender (1 = Male, 2 = Female)
     * - Digits 8-11: Serial Sequence (4 digits)
     */
    public static function generateStudentCode(Batch $batch, ?Course $course = null, ?string $gender = null): string
    {
        $year = self::resolveAcademicYearCode($batch);
        $batchNum = self::resolveBatchNumberCode($batch);
        $courseCode = self::resolveCourseCode($course, $batch->course_id);
        $genderCode = self::resolveGenderCode($gender);

        $prefix = "{$year}{$batchNum}{$courseCode}{$genderCode}";

        $existingCodes = self::where('student_code', 'like', "{$prefix}%")
            ->pluck('student_code');

        $maxSeq = 0;
        foreach ($existingCodes as $c) {
            $clean = preg_replace('/\D/', '', $c);
            if (strlen($clean) >= 11) {
                $seq = (int) substr($clean, 7, 4);
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        $nextSeq = $maxSeq + 1;
        $candidate = "{$prefix}" . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        while (self::where('student_code', $candidate)->exists()) {
            $nextSeq++;
            $candidate = "{$prefix}" . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    /**
     * Dynamically update student ID when Course or Batch changes (Transfer or Readmission)
     * Format: YYBBCCGRRRR (Digits 1-2: Year, 3-4: Batch, 5-6: Course Code, 7: Gender, 8-11: Serial)
     */
    public function updateCodeForTransferOrReadmission(?Batch $newBatch = null, ?Course $newCourse = null): string
    {
        $currentCode = preg_replace('/\D/', '', (string)$this->student_code);

        // Digits 1-2: Year
        if ($newBatch) {
            $yearCode = self::resolveAcademicYearCode($newBatch);
        } elseif (strlen($currentCode) >= 2) {
            $yearCode = substr($currentCode, 0, 2);
        } else {
            $yearCode = date('y');
        }

        // Digits 3-4: Batch
        if ($newBatch) {
            $batchCode = self::resolveBatchNumberCode($newBatch);
        } elseif (strlen($currentCode) >= 4) {
            $batchCode = substr($currentCode, 2, 2);
        } else {
            $batchCode = '01';
        }

        // Digits 5-6: Course Code
        if ($newCourse) {
            $courseCode = self::resolveCourseCode($newCourse, $newCourse->id);
        } elseif (strlen($currentCode) >= 6) {
            $courseCode = substr($currentCode, 4, 2);
        } else {
            $courseCode = '01';
        }

        // Digit 7: Gender
        $genderCode = self::resolveGenderCode($this->gender);

        // Digits 8-11: Serial
        if (strlen($currentCode) >= 11) {
            $seqNo = substr($currentCode, 7, 4);
        } else {
            $seqNo = str_pad(($this->id % 10000) ?: 1, 4, '0', STR_PAD_LEFT);
        }

        $prefix = "{$yearCode}{$batchCode}{$courseCode}{$genderCode}";
        $newCode = "{$prefix}{$seqNo}";

        // If collision with another student, find next available sequence
        if (self::where('student_code', $newCode)->where('id', '!=', $this->id)->exists()) {
            $seq = (int) $seqNo;
            while (self::where('student_code', $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT))->where('id', '!=', $this->id)->exists()) {
                $seq++;
            }
            $newCode = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
        }

        $oldCode = $this->student_code;

        if ($oldCode !== $newCode) {
            $this->student_code = $newCode;
            $this->save();

            // Update user email if it was linked to the old student_code
            if ($this->user) {
                if ($this->email) {
                    $existingUserWithEmail = User::where('email', $this->email)->where('id', '!=', $this->user->id)->first();
                    if (!$existingUserWithEmail) {
                        $this->user->email = $this->email;
                        $this->user->save();
                    }
                } elseif (str_contains($this->user->email, '@iom.student')) {
                    $this->user->email = "{$newCode}@iom.student";
                    $this->user->save();
                }
            }

            // Also update any support tickets with old student_id
            \App\Models\SupportTicket::where('student_id', $oldCode)->update(['student_id' => $newCode]);
        }

        return $newCode;
    }

    public function isProfileCompleted(): bool
    {
        if ($this->is_common_account) {
            return true;
        }

        if ($this->profile_completed_percent === null) {
            return $this->calculateProfileCompletion() >= 95;
        }
        return (int) $this->profile_completed_percent >= 95;
    }

    public function getMissingProfileFields(): array
    {
        $fields = self::profileFieldsDefinition();
        $missing = [];

        foreach ($fields as $field => $label) {
            $val = trim((string) ($this->{$field} ?? ''));
            if ($val === '') {
                $missing[$field] = $label;
            }
        }

        return $missing;
    }
}
