<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramActivity;
use App\Models\Course;
use App\Models\Batch;
use Illuminate\Http\Request;

class ProgramActivityController extends Controller
{
    public function index()
    {
        $activities = ProgramActivity::with(['course', 'batch'])->orderBy('id')->get();
        $courses    = Course::where('is_active', true)->orderBy('name')->get();
        $batches    = Batch::where('status', 'ACTIVE')->orderBy('name')->get();

        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        return view('admin.program-activities.index', compact('activities', 'courses', 'batches', 'months'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'semester_name'  => 'required|string|max:100',
            'course_id'      => 'nullable|exists:courses,id',
            'batch_id'       => 'nullable|exists:batches,id',
            'starting_month' => 'required|string|max:50',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
        ]);

        ProgramActivity::create([
            'semester_name'  => $validated['semester_name'],
            'course_id'      => $validated['course_id'] ?: null,
            'batch_id'       => $validated['batch_id'] ?: null,
            'starting_month' => $validated['starting_month'],
            'start_date'     => $validated['start_date'] ?: null,
            'end_date'       => $validated['end_date'] ?: null,
            'is_active'      => true,
        ]);

        return back()->with('success', 'প্রোগ্রাম অ্যাক্টিভিটি সফলভাবে তৈরি হয়েছে। (Program Activity created successfully)');
    }

    public function update(Request $request, ProgramActivity $programActivity)
    {
        $validated = $request->validate([
            'semester_name'  => 'required|string|max:100',
            'course_id'      => 'nullable|exists:courses,id',
            'batch_id'       => 'nullable|exists:batches,id',
            'starting_month' => 'required|string|max:50',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
        ]);

        $programActivity->update([
            'semester_name'  => $validated['semester_name'],
            'course_id'      => $validated['course_id'] ?: null,
            'batch_id'       => $validated['batch_id'] ?: null,
            'starting_month' => $validated['starting_month'],
            'start_date'     => $validated['start_date'] ?: null,
            'end_date'       => $validated['end_date'] ?: null,
        ]);

        return back()->with('success', 'প্রোগ্রাম অ্যাক্টিভিটি সফলভাবে আপডেট হয়েছে। (Program Activity updated successfully)');
    }

    public function destroy(ProgramActivity $programActivity)
    {
        $programActivity->delete();
        return back()->with('success', 'প্রোগ্রাম অ্যাক্টিভিটি মুছে ফেলা হয়েছে। (Program Activity deleted)');
    }
}
