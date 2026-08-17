<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubjectRetake;
use App\Models\Student;
use App\Models\Subject;
use App\Services\AccountingService;
use Illuminate\Http\Request;

class SubjectRetakeController extends Controller
{
    public function index()
    {
        $retakes  = SubjectRetake::with(['student', 'subject'])->latest()->get();
        $students = Student::where('status', 'ACTIVE')->orderBy('name')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        return view('admin.retakes.index', compact('retakes', 'students', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'  => 'required|exists:students,id',
            'subject_id'  => 'required|exists:subjects,id',
            'retake_type' => 'required|in:EXAM_ONLY,CLASS_EXAM,FULL_RESTART',
            'notes'       => 'nullable|string',
        ]);

        SubjectRetake::create([
            'student_id'  => $validated['student_id'],
            'subject_id'  => $validated['subject_id'],
            'retake_type' => $validated['retake_type'],
            'status'      => 'PENDING',
            'reason'      => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Subject retake registered. Admin approval & fee will be set upon review.');
    }

    /**
     * Admin approves a retake and sets the retake fee — invoice generated here.
     */
    public function approve(Request $request, SubjectRetake $retake)
    {
        $validated = $request->validate([
            'retake_fee' => 'required|numeric|min:0',
            'notes'      => 'nullable|string',
        ]);

        $retake->update([
            'status'     => 'IN_PROGRESS',
            'retake_fee' => $validated['retake_fee'],
            'reason'     => $validated['notes'] ?? $retake->reason,
        ]);

        // Generate Retake Fee Invoice with the admin-set fee
        $student = $retake->student;
        AccountingService::createRetakeInvoice($student, $retake, (float) $validated['retake_fee']);

        return back()->with('success', "Retake approved! Fee: ৳" . number_format($validated['retake_fee'], 0) . " Invoice generated.");
    }
}
