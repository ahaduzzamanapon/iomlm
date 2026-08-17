<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromotionRecord;
use App\Models\Student;
use App\Models\Batch;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = PromotionRecord::with(['student', 'fromBatch', 'toBatch', 'promoter'])->latest()->get();
        $students = Student::where('status', 'ACTIVE')->orderBy('name')->get();
        $batches = Batch::where('status', 'ACTIVE')->orderBy('name')->get();
        return view('admin.promotions.index', compact('promotions', 'students', 'batches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'    => 'required|exists:students,id',
            'from_batch_id' => 'required|exists:batches,id',
            'to_batch_id'   => 'required|exists:batches,id',
            'decision'      => 'required|in:PROMOTED,FORCE_PROMOTED,HELD_BACK',
            'notes'         => 'nullable|string',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        $promotion = PromotionRecord::create([
            'student_id'    => $validated['student_id'],
            'from_batch_id' => $validated['from_batch_id'],
            'to_batch_id'   => $validated['to_batch_id'],
            'decision'      => $validated['decision'],
            'promoted_by'   => auth()->id(),
            'notes'         => $validated['notes'] ?? null,
        ]);

        if (in_array($validated['decision'], ['PROMOTED', 'FORCE_PROMOTED'])) {
            $enrollment = $student->enrollments()->where('status', 'ACTIVE')->first();
            if ($enrollment) {
                \App\Services\AccountingService::createSemesterInvoice($student, $enrollment);
            }
        }

        return back()->with('success', 'Promotion decision recorded & Auto Semester Fee Invoice generated if promoted!');
    }
}
