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
        $validated = $request->validate([
            'subject_module_id' => 'required|exists:subject_modules,id',
            'title'             => 'required|string|max:200',
            'type'              => 'required|in:VIDEO,RECORDING,PDF,AUDIO,SLIDES,NOTES,ASSIGNMENT,QUIZ,PRACTICAL,LINK',
            'url'               => 'nullable|string',
            'content'           => 'nullable|string',
        ]);

        LearningResource::create([
            'subject_module_id' => $validated['subject_module_id'],
            'title'             => $validated['title'],
            'type'              => $validated['type'],
            'url'               => $validated['url'] ?? null,
            'content'           => $validated['content'] ?? null,
            'created_by'        => auth()->id(),
        ]);

        return back()->with('success', 'Learning resource uploaded successfully.');
    }

    public function destroy(LearningResource $resource)
    {
        $resource->delete();
        return back()->with('success', 'Resource removed.');
    }
}
