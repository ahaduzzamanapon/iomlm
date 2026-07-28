<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmissionForm;
use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class AdmissionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = AdmissionForm::with(['student', 'interestedCourse', 'reviewer']);

        if ($status) {
            $query->where('status', $status);
        }

        $admissions = $query->latest()->get();
        return view('admin.admissions.index', compact('admissions', 'status'));
    }

    public function create()
    {
        $courses = Course::where('is_active', true)->orderBy('name')->get();
        return view('admin.admissions.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:200',
            'email'                => 'nullable|email|unique:students,email',
            'phone'                => 'required|string|max:30',
            'date_of_birth'        => 'nullable|date',
            'gender'               => 'nullable|string',
            'blood_group'          => 'nullable|string',
            'national_id'          => 'nullable|string|max:50',
            'address'              => 'nullable|string',
            'father_name'          => 'nullable|string|max:200',
            'mother_name'          => 'nullable|string|max:200',
            'guardian_name'        => 'nullable|string|max:200',
            'guardian_phone'       => 'nullable|string|max:30',
            'ssc_gpa'              => 'nullable|numeric|min:0|max:5',
            'hsc_gpa'              => 'nullable|numeric|min:0|max:5',
            'interested_course_id' => 'required|exists:courses,id',
            'lead_source'          => 'nullable|string',
            'discount_percent'     => 'nullable|numeric|min:0|max:100',
            'notes'                => 'nullable|string',
        ]);

        // Create Student as LEAD/PENDING
        $student = Student::create([
            'name'             => $validated['name'],
            'email'            => $validated['email'] ?? null,
            'phone'            => $validated['phone'],
            'date_of_birth'    => $validated['date_of_birth'] ?? null,
            'gender'           => $validated['gender'] ?? null,
            'blood_group'      => $validated['blood_group'] ?? null,
            'national_id'      => $validated['national_id'] ?? null,
            'address'          => $validated['address'] ?? null,
            'father_name'      => $validated['father_name'] ?? null,
            'mother_name'      => $validated['mother_name'] ?? null,
            'guardian_name'    => $validated['guardian_name'] ?? null,
            'guardian_phone'   => $validated['guardian_phone'] ?? null,
            'ssc_gpa'          => $validated['ssc_gpa'] ?? null,
            'hsc_gpa'          => $validated['hsc_gpa'] ?? null,
            'status'           => 'PENDING',
        ]);

        // Create Admission Form (Attempt 1)
        $form = AdmissionForm::create([
            'student_id'           => $student->id,
            'interested_course_id' => $validated['interested_course_id'],
            'attempt_no'           => 1,
            'lead_source'          => $validated['lead_source'] ?? 'Direct',
            'discount_percent'     => $validated['discount_percent'] ?? 0,
            'status'               => 'PENDING',
            'notes'                => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.admissions.show', $form)
            ->with('success', 'Admission application created successfully. Pending review.');
    }

    public function show(AdmissionForm $admission)
    {
        $admission->load(['student', 'interestedCourse', 'reviewer']);
        $activeBatches = Batch::where('course_id', $admission->interested_course_id)
            ->where('status', 'ACTIVE')
            ->get();

        return view('admin.admissions.show', compact('admission', 'activeBatches'));
    }

    public function approve(Request $request, AdmissionForm $admission)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
        ]);

        $student = $admission->student;
        $batch = Batch::findOrFail($request->input('batch_id'));

        // Generate Student Code (e.g. STD-2026-005)
        if (empty($student->student_code)) {
            $nextId = Student::whereNotNull('student_code')->count() + 1;
            $student->student_code = 'STD-' . date('Y') . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        }

        $student->status = 'ACTIVE';
        $student->save();

        // Update Admission Form
        $admission->update([
            'status'      => 'APPROVED',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Create Enrollment
        Enrollment::create([
            'student_id'        => $student->id,
            'batch_id'          => $batch->id,
            'course_id'         => $batch->course_id,
            'semester_id'       => $batch->semesterPosition?->current_semester_id,
            'admission_form_id' => $admission->id,
            'enrolled_at'       => now()->toDateString(),
            'status'            => 'ACTIVE',
        ]);

        return back()->with('success', "Admission APPROVED! Student activated with Code: {$student->student_code} and enrolled into {$batch->name}.");
    }

    public function reject(Request $request, AdmissionForm $admission)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $admission->update([
            'status'           => 'REJECTED',
            'rejection_reason' => $request->input('rejection_reason'),
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        return back()->with('success', 'Admission application rejected. Reason logged for student re-apply.');
    }
}
