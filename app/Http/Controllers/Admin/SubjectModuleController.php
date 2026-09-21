<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SubjectModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubjectModuleController extends Controller
{
    public function store(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'category'                 => 'nullable|string|max:100',
            'folder_name'              => 'nullable|string|max:100',
            'title'                    => 'required|string|max:250',
            'sequence_no'              => 'required|integer|min:1',
            'description'              => 'nullable|string',
            'attachment'               => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,jpg,jpeg,png|max:51200', // 50MB
            'drive_link'               => 'nullable|url|max:500',
            'recorded_url'             => 'nullable|string|max:500',
            'embed_code'               => 'nullable|string',
            'is_hidden'                => 'nullable|boolean',
            'is_locked_until_previous' => 'nullable|boolean',
        ]);

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('modules/attachments', 'public');
        }

        SubjectModule::create([
            'subject_id'               => $subject->id,
            'category'                 => $validated['category'] ?? null,
            'folder_name'              => $validated['folder_name'] ?? 'General',
            'sequence_no'              => $validated['sequence_no'],
            'title'                    => $validated['title'],
            'description'              => $validated['description'] ?? null,
            'file_path'                => $filePath,
            'drive_link'               => $validated['drive_link'] ?? null,
            'recorded_url'             => $validated['recorded_url'] ?? null,
            'embed_code'               => $validated['embed_code'] ?? null,
            'is_hidden'                => $request->boolean('is_hidden', false),
            'is_locked_until_previous' => $request->boolean('is_locked_until_previous', false),
            'is_active'                => true,
        ]);

        return back()->with('success', 'মডিউল সফলভাবে যুক্ত করা হয়েছে।');
    }

    public function update(Request $request, SubjectModule $module)
    {
        $validated = $request->validate([
            'category'                 => 'nullable|string|max:100',
            'folder_name'              => 'nullable|string|max:100',
            'title'                    => 'required|string|max:250',
            'sequence_no'              => 'required|integer|min:1',
            'description'              => 'nullable|string',
            'attachment'               => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,jpg,jpeg,png|max:51200',
            'drive_link'               => 'nullable|url|max:500',
            'recorded_url'             => 'nullable|string|max:500',
            'embed_code'               => 'nullable|string',
            'is_hidden'                => 'nullable|boolean',
            'is_locked_until_previous' => 'nullable|boolean',
        ]);

        $filePath = $module->file_path;
        if ($request->hasFile('attachment')) {
            if ($module->file_path && Storage::disk('public')->exists($module->file_path)) {
                Storage::disk('public')->delete($module->file_path);
            }
            $filePath = $request->file('attachment')->store('modules/attachments', 'public');
        }

        $module->update([
            'category'                 => $validated['category'] ?? null,
            'folder_name'              => $validated['folder_name'] ?? 'General',
            'sequence_no'              => $validated['sequence_no'],
            'title'                    => $validated['title'],
            'description'              => $validated['description'] ?? null,
            'file_path'                => $filePath,
            'drive_link'               => $validated['drive_link'] ?? null,
            'recorded_url'             => $validated['recorded_url'] ?? null,
            'embed_code'               => $validated['embed_code'] ?? null,
            'is_hidden'                => $request->boolean('is_hidden', false),
            'is_locked_until_previous' => $request->boolean('is_locked_until_previous', false),
        ]);

        return back()->with('success', 'মডিউল সফলভাবে আপডেট করা হয়েছে।');
    }

    public function toggleHidden(SubjectModule $module)
    {
        $module->update(['is_hidden' => !$module->is_hidden]);
        $statusText = $module->is_hidden ? 'শিক্ষার্থীদের কাছ থেকে লুকানো (Hidden)' : 'শিক্ষার্থীদের জন্য দৃশ্যমান (Visible)';

        return back()->with('success', "মডিউল '{$module->title}' এখন {$statusText} করা হয়েছে।");
    }

    public function clone(SubjectModule $module)
    {
        $cloned = $module->cloneModule();
        return back()->with('success', "মডিউল '{$module->title}' সফলভাবে ক্লোন করা হয়েছে।");
    }

    public function destroy(SubjectModule $module)
    {
        if ($module->file_path && Storage::disk('public')->exists($module->file_path)) {
            Storage::disk('public')->delete($module->file_path);
        }

        $module->delete();
        return back()->with('success', 'মডিউল সফলভাবে অপসারণ করা হয়েছে।');
    }
}
