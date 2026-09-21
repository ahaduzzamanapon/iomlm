<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubjectCategory;
use Illuminate\Http\Request;

class SubjectCategoryController extends Controller
{
    public function index()
    {
        $categories = SubjectCategory::withCount('subjects')->latest()->get();
        return view('admin.subject_categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'code'        => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ]);

        SubjectCategory::create([
            'name'        => $validated['name'],
            'code'        => $validated['code'] ? strtoupper($validated['code']) : null,
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'বিষয় ক্যাটাগরি সফলভাবে তৈরি করা হয়েছে।');
    }

    public function update(Request $request, SubjectCategory $subjectCategory)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'code'        => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ]);

        $subjectCategory->update([
            'name'        => $validated['name'],
            'code'        => $validated['code'] ? strtoupper($validated['code']) : null,
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'বিষয় ক্যাটাগরি সফলভাবে আপডেট করা হয়েছে।');
    }

    public function destroy(SubjectCategory $subjectCategory)
    {
        // Unlink subjects
        $subjectCategory->subjects()->update(['category_id' => null]);
        $subjectCategory->delete();

        return back()->with('success', 'বিষয় ক্যাটাগরি সফলভাবে অপসারণ করা হয়েছে।');
    }
}
