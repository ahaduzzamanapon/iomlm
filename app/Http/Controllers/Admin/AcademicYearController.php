<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::with(['sessions' => function($q) {
            $q->orderBy('start_date')->orderBy('name');
        }])->latest()->get();

        $allSessions = AcademicSession::with('academicYear')
            ->orderByDesc('id')
            ->get();

        return view('admin.academic_years.index', compact('academicYears', 'allSessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        if ($request->boolean('is_active')) {
            AcademicYear::query()->update(['is_active' => false]);
        }

        AcademicYear::create([
            'name'       => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Academic Year created successfully.');
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        if ($request->boolean('is_active')) {
            AcademicYear::where('id', '!=', $academicYear->id)->update(['is_active' => false]);
        }

        $academicYear->update([
            'name'       => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'is_active'  => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Academic Year updated successfully.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();
        return back()->with('success', 'Academic Year deleted.');
    }

    public function toggleStatus(AcademicYear $academicYear)
    {
        $newStatus = !$academicYear->is_active;

        if ($newStatus) {
            AcademicYear::where('id', '!=', $academicYear->id)->update(['is_active' => false]);
        }

        $academicYear->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'Active' : 'Inactive';
        return back()->with('success', "Academic Year status changed to {$statusText}.");
    }

    public function storeSession(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $isActive = $request->boolean('is_active', true);
        if ($isActive) {
            AcademicSession::where('academic_year_id', $academicYear->id)->update(['is_active' => false]);
        }

        AcademicSession::create([
            'academic_year_id' => $academicYear->id,
            'name'             => $validated['name'],
            'start_date'       => $validated['start_date'] ?? null,
            'end_date'         => $validated['end_date'] ?? null,
            'is_active'        => $isActive,
        ]);

        return back()->with('success', "সেশন '{$validated['name']}' সফলভাবে তৈরি হয়েছে।");
    }

    public function storeDirectSession(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name'             => 'required|string|max:100',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
        ]);

        $isActive = $request->boolean('is_active', true);
        if ($isActive) {
            AcademicSession::where('academic_year_id', $validated['academic_year_id'])->update(['is_active' => false]);
        }

        AcademicSession::create([
            'academic_year_id' => $validated['academic_year_id'],
            'name'             => $validated['name'],
            'start_date'       => $validated['start_date'] ?? null,
            'end_date'         => $validated['end_date'] ?? null,
            'is_active'        => $isActive,
        ]);

        return back()->with('success', "সেশন '{$validated['name']}' সফলভাবে যোগ করা হয়েছে।");
    }

    public function updateSession(Request $request, AcademicSession $academicSession)
    {
        $validated = $request->validate([
            'academic_year_id' => 'sometimes|exists:academic_years,id',
            'name'             => 'required|string|max:100',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
        ]);

        $targetYearId = $validated['academic_year_id'] ?? $academicSession->academic_year_id;
        $isActive = $request->boolean('is_active');
        if ($isActive) {
            AcademicSession::where('academic_year_id', $targetYearId)
                ->where('id', '!=', $academicSession->id)
                ->update(['is_active' => false]);
        }

        $academicSession->update([
            'academic_year_id' => $targetYearId,
            'name'             => $validated['name'],
            'start_date'       => $validated['start_date'] ?? null,
            'end_date'         => $validated['end_date'] ?? null,
            'is_active'        => $isActive,
        ]);

        return back()->with('success', "সেশন '{$academicSession->name}' তথ্য সফলভাবে আপডেট হয়েছে।");
    }

    public function toggleSessionStatus(AcademicSession $academicSession)
    {
        $newStatus = !$academicSession->is_active;

        if ($newStatus) {
            // Deactivate other sessions in the same academic year so only one is active
            AcademicSession::where('academic_year_id', $academicSession->academic_year_id)
                ->where('id', '!=', $academicSession->id)
                ->update(['is_active' => false]);
        }

        $academicSession->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'সক্রিয় (Active)' : 'নিষ্ক্রিয় (Inactive)';
        return back()->with('success', "সেশন '{$academicSession->name}' স্ট্যাটাস {$statusText} করা হয়েছে।");
    }

    public function destroySession(AcademicSession $academicSession)
    {
        $name = $academicSession->name;
        $academicSession->delete();
        return back()->with('success', "সেশন '{$name}' মুছে ফেলা হয়েছে।");
    }
}

