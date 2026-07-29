<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SubjectModule;
use Illuminate\Http\Request;

class SubjectModuleController extends Controller
{
    public function store(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'category'    => 'nullable|string|max:100',
            'title'       => 'required|string|max:250',
            'sequence_no' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        SubjectModule::create([
            'subject_id'               => $subject->id,
            'category'                 => $validated['category'] ?? null,
            'sequence_no'              => $validated['sequence_no'],
            'title'                    => $validated['title'],
            'description'              => $validated['description'] ?? null,
            'is_locked_until_previous' => $request->boolean('is_locked_until_previous', true),
            'is_active'                => true,
        ]);

        return back()->with('success', 'Module added to subject.');
    }

    public function update(Request $request, SubjectModule $module)
    {
        $validated = $request->validate([
            'category'    => 'nullable|string|max:100',
            'title'       => 'required|string|max:250',
            'sequence_no' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $module->update([
            'category'                 => $validated['category'] ?? null,
            'title'                    => $validated['title'],
            'sequence_no'              => $validated['sequence_no'],
            'description'              => $validated['description'] ?? null,
            'is_locked_until_previous' => $request->boolean('is_locked_until_previous'),
        ]);

        return back()->with('success', 'Module updated.');
    }

    public function destroy(SubjectModule $module)
    {
        $module->delete();
        return back()->with('success', 'Module deleted.');
    }
}
