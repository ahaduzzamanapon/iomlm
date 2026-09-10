<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $guarded = [];

    // ── Relationships ───────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
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

    public function isProfileCompleted(): bool
    {
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
