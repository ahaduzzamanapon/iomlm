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
}
