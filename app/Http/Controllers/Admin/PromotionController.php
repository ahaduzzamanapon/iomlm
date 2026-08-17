<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromotionRecord;
use App\Models\Student;
use App\Models\Semester;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = PromotionRecord::with(['student', 'fromSemester', 'toSemester', 'decidedBy'])->latest()->get();
        $students   = Student::where('status', 'ACTIVE')->orderBy('name')->get();
        $semesters  = Semester::orderBy('course_id')->orderBy('sequence_no')->get();
        return view('admin.promotions.index', compact('promotions', 'students', 'semesters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'      => 'required|exists:students,id',
            'from_semester_id'=> 'nullable|exists:semesters,id',
            'to_semester_id'  => 'nullable|exists:semesters,id',
            'decision'        => 'required|in:PROMOTED,FORCE_PROMOTED,HELD_BACK',
            'notes'           => 'nullable|string',
        ]);

        $student    = Student::findOrFail($validated['student_id']);
        $enrollment = $student->enrollments()->where('status', 'ACTIVE')->first();

        PromotionRecord::create([
            'student_id'       => $validated['student_id'],
            'enrollment_id'    => $enrollment?->id,
            'from_semester_id' => $validated['from_semester_id'] ?? null,
            'to_semester_id'   => $validated['to_semester_id'] ?? null,
            'decision'         => $validated['decision'],
            'decided_by'       => auth()->id(),
            'notes'            => $validated['notes'] ?? null,
        ]);

        if (in_array($validated['decision'], ['PROMOTED', 'FORCE_PROMOTED']) && $enrollment) {
            \App\Services\AccountingService::createSemesterInvoice($student, $enrollment);
        }

        return back()->with('success', 'Promotion decision recorded & Auto Semester Fee Invoice generated if promoted!');
    }
}
