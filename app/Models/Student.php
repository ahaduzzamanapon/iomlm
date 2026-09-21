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
     * Dynamically update student ID when Course or Batch changes (Transfer or Readmission)
     * Format: YYBBCCGRRRR (Digits 1-2: Year, 3-4: Batch, 5-6: Course Code, 7: Gender, 8-11: Serial)
     */
    public function updateCodeForTransferOrReadmission(?Batch $newBatch = null, ?Course $newCourse = null): string
    {
        $currentCode = preg_replace('/\D/', '', (string)$this->student_code);

        // If code is not set or not in standard format, generate fresh
        if (strlen($currentCode) < 11) {
            $yearCode = date('y');
            $genderCode = in_array(strtolower(trim($this->gender ?? '')), ['female', '2', 'f', 'নারি', 'মহিলা']) ? '2' : '1';
            $seqNo = str_pad(self::count() + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $yearCode = substr($currentCode, 0, 2);
            $genderCode = substr($currentCode, 6, 1);
            $seqNo = substr($currentCode, 7, 4);
        }

        // Determine Batch Code (Digits 3 & 4)
        if ($newBatch) {
            $batchNum = 1;
            if (!empty($newBatch->batch_code) && preg_match('/\d+/', $newBatch->batch_code, $m)) {
                $batchNum = (int)$m[0];
            } elseif (!empty($newBatch->name) && preg_match('/\d+/', $newBatch->name, $m)) {
                $batchNum = (int)$m[0];
            } else {
                $batchNum = $newBatch->id;
            }
            $batchCode = str_pad($batchNum % 100, 2, '0', STR_PAD_LEFT);
        } elseif (strlen($currentCode) >= 4) {
            $batchCode = substr($currentCode, 2, 2);
        } else {
            $batchCode = '01';
        }

        // Determine Course Code (Digits 5 & 6)
        if ($newCourse) {
            if (!empty($newCourse->code)) {
                $digits = preg_replace('/\D/', '', $newCourse->code);
                $courseCode = !empty($digits) ? str_pad(substr($digits, 0, 2), 2, '0', STR_PAD_LEFT) : str_pad(($newCourse->id % 100), 2, '0', STR_PAD_LEFT);
            } else {
                $courseCode = str_pad(($newCourse->id % 100), 2, '0', STR_PAD_LEFT);
            }
        } elseif (strlen($currentCode) >= 6) {
            $courseCode = substr($currentCode, 4, 2);
        } else {
            $courseCode = '01';
        }

        $oldCode = $this->student_code;
        $newCode = "{$yearCode}{$batchCode}{$courseCode}{$genderCode}{$seqNo}";

        if ($oldCode !== $newCode) {
            $this->student_code = $newCode;
            $this->save();

            // Update user email if it was linked to the old student_code
            if ($this->user) {
                if (str_contains($this->user->email, '@iom.student')) {
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
