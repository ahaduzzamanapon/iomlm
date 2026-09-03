<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LearningResource;
use App\Models\SubjectModule;
use Illuminate\Http\Request;

class LearningResourceController extends Controller
{
    public function index()
    {
        $resources = LearningResource::with('module.subject')->latest()->get();
        $modules = SubjectModule::with('subject')->orderBy('title')->get();
        return view('teacher.resources.index', compact('resources', 'modules'));
    }

    public function store(Request $request)
    {
        $moduleId = $request->input('module_id') ?? $request->input('subject_module_id');

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'type'  => 'required|string',
            'url'   => 'nullable|string|max:1000',
        ]);

        if (!$moduleId || !SubjectModule::where('id', $moduleId)->exists()) {
            return back()->withErrors(['module_id' => 'Please select a valid subject module.']);
        }

        $type = match($validated['type']) {
            'RECORDING'  => 'VIDEO',
            'ASSIGNMENT' => 'NOTES',
            'QUIZ'       => 'NOTES',
            'PRACTICAL'  => 'NOTES',
            default      => in_array($validated['type'], ['VIDEO', 'PDF', 'AUDIO', 'NOTES', 'SLIDES', 'LINK']) ? $validated['type'] : 'LINK',
        };

        LearningResource::create([
            'module_id' => $moduleId,
            'title'     => $validated['title'],
            'type'      => $type,
            'url'       => $validated['url'] ?? '#',
        ]);

        return back()->with('success', 'Learning resource uploaded successfully.');
    }

    public function destroy(LearningResource $resource)
    {
        $resource->delete();
        return back()->with('success', 'Resource removed successfully.');
    }
}
