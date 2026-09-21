<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_admission_open' => 'boolean',
        'admission_fee'     => 'float',
        'monthly_fee'       => 'float',
    ];

    public function scopeAdmissionOpen($query)
    {
        return $query->where('status', 'ACTIVE')->where('is_admission_open', true);
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function semesterPosition()
    {
        return $this->hasOne(BatchSemesterPosition::class, 'batch_id');
    }

    public function timelines()
    {
        return $this->hasMany(Timeline::class, 'batch_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'batch_id');
    }

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class, 'batch_id');
    }

    public function routineEntries()
    {
        return $this->hasMany(RoutineEntry::class, 'batch_id');
    }

    public function getEffectiveEmailTemplate(): string
    {
        if (!empty($this->email_template)) {
            return $this->email_template;
        }

        return "আসসালামু আলাইকুম {name},\n\n"
            . "আলহামদুলিল্লাহ! ইসলামিক অনলাইন মাদ্রাসায় \"{course}\" ({batch}) কোর্সে আপনার ভর্তি সফলভাবে অনুমোদিত ও নিশ্চিত হয়েছে।\n\n"
            . "আপনার অফিসিয়াল লগইন তথ্য:\n"
            . "----------------------------------------\n"
            . "• স্টুডেন্ট আইডি / রোল: {roll}\n"
            . "• লগইন পাসওয়ার্ড: {password}\n"
            . "• পোর্টাল লিংক: {login_url}\n"
            . "----------------------------------------\n\n"
            . "আপনি আপনার স্টুডেন্ট আইডি অথবা ইমেইল এবং পাসওয়ার্ড ব্যবহার করে স্টুডেন্ট পোর্টালে লগইন করতে পারবেন।\n"
            . "নিয়মিত ক্লাসে অংশগ্রহণ ও শিক্ষণ সামগ্রী পেতে এখনই পোর্টালে প্রবেশ করুন।";
    }

    public function getEffectiveSmsTemplate(): string
    {
        if (!empty($this->sms_template)) {
            return $this->sms_template;
        }

        return "আইওএম ভর্তি কনফার্ম! নাম: {name}, কোর্স: {course}, রোল: {roll}, পাসওয়ার্ড: {password}, লগইন: {login_url}";
    }
}
