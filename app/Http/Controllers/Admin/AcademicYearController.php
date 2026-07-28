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
        $academicYears = AcademicYear::with('sessions')->latest()->get();
        return view('admin.academic_years.index', compact('academicYears'));
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

    public function storeSession(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        AcademicSession::create([
            'academic_year_id' => $academicYear->id,
            'name'             => $validated['name'],
            'is_active'        => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Academic Session added.');
    }
}
