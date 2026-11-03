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
            'videos'                   => 'nullable|array',
            'videos.*.title'           => 'nullable|string|max:250',
            'videos.*.url'             => 'nullable|string|max:1000',
            'videos.*.embed_code'      => 'nullable|string',
            'videos.*.file'            => 'nullable|file|mimes:mp4,webm,ogg,mov,avi,mkv|max:512000',
            'videos.*.existing_file'   => 'nullable|string|max:500',
            'is_hidden'                => 'nullable|boolean',
            'is_locked_until_previous' => 'nullable|boolean',
        ]);

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('modules/attachments', 'public');
        }

        $recordedVideos = $this->processRecordedVideos($request);
        $firstVideo = $recordedVideos[0] ?? null;

        SubjectModule::create([
            'subject_id'               => $subject->id,
            'category'                 => $validated['category'] ?? null,
            'folder_name'              => $validated['folder_name'] ?? 'General',
            'sequence_no'              => $validated['sequence_no'],
            'title'                    => $validated['title'],
            'description'              => $validated['description'] ?? null,
            'file_path'                => $filePath,
            'drive_link'               => $validated['drive_link'] ?? null,
            'recorded_url'             => $firstVideo['url'] ?? ($validated['recorded_url'] ?? null),
            'embed_code'               => $firstVideo['embed_code'] ?? ($validated['embed_code'] ?? null),
            'recorded_videos'          => !empty($recordedVideos) ? $recordedVideos : null,
            'is_hidden'                => $request->boolean('is_hidden', false),
            'is_locked_until_previous' => $request->boolean('is_locked_until_previous', false),
            'is_active'                => true,
        ]);

        return back()->with('success', 'মডিউল সফলভাবে সংরক্ষণ করা হয়েছে।');
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
            'videos'                   => 'nullable|array',
            'videos.*.title'           => 'nullable|string|max:250',
            'videos.*.url'             => 'nullable|string|max:1000',
            'videos.*.embed_code'      => 'nullable|string',
            'videos.*.file'            => 'nullable|file|mimes:mp4,webm,ogg,mov,avi,mkv|max:512000',
            'videos.*.existing_file'   => 'nullable|string|max:500',
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

        $recordedVideos = $this->processRecordedVideos($request, $module);
        $firstVideo = $recordedVideos[0] ?? null;

        $module->update([
            'category'                 => $validated['category'] ?? null,
            'folder_name'              => $validated['folder_name'] ?? 'General',
            'sequence_no'              => $validated['sequence_no'],
            'title'                    => $validated['title'],
            'description'              => $validated['description'] ?? null,
            'file_path'                => $filePath,
            'drive_link'               => $validated['drive_link'] ?? null,
            'recorded_url'             => $request->has('videos') ? ($firstVideo['url'] ?? null) : $module->recorded_url,
            'embed_code'               => $request->has('videos') ? ($firstVideo['embed_code'] ?? null) : $module->embed_code,
            'recorded_videos'          => $request->has('videos') ? (!empty($recordedVideos) ? $recordedVideos : null) : $module->recorded_videos,
            'is_hidden'                => $request->boolean('is_hidden', false),
            'is_locked_until_previous' => $request->boolean('is_locked_until_previous', false),
        ]);

        return back()->with('success', 'মডিউল সফলভাবে আপডেট করা হয়েছে।');
    }

    /**
     * Process multiple recorded videos from form request
     */
    protected function processRecordedVideos(Request $request, ?SubjectModule $existingModule = null): array
    {
        $recordedVideos = [];
        $existingVideos = $existingModule ? ($existingModule->videos ?? []) : [];
        $existingFilesKept = [];

        if ($request->has('videos') && is_array($request->input('videos'))) {
            foreach ($request->input('videos') as $index => $vidData) {
                $title = trim($vidData['title'] ?? '');
                $url = trim($vidData['url'] ?? '');
                $embedCode = trim($vidData['embed_code'] ?? '');
                $existingFile = $vidData['existing_file'] ?? null;
                $filePath = $existingFile;

                // Check for new video file upload
                if ($request->hasFile("videos.{$index}.file")) {
                    $file = $request->file("videos.{$index}.file");
                    if ($file && $file->isValid()) {
                        // Delete old file if replacing
                        if ($existingFile && Storage::disk('public')->exists($existingFile)) {
                            Storage::disk('public')->delete($existingFile);
                        }
                        $filePath = $file->store('modules/videos', 'public');
                    }
                }

                if ($filePath) {
                    $existingFilesKept[] = $filePath;
                }

                // If any content is provided
                if (!empty($title) || !empty($url) || !empty($embedCode) || !empty($filePath)) {
                    $recordedVideos[] = [
                        'id'         => $vidData['id'] ?? ('vid_' . uniqid()),
                        'title'      => !empty($title) ? $title : ('ক্লাস রেকর্ড ' . (count($recordedVideos) + 1)),
                        'url'        => !empty($url) ? $url : null,
                        'embed_code' => !empty($embedCode) ? $embedCode : null,
                        'file_path'  => $filePath,
                    ];
                }
            }
        }

        // Clean up orphaned video files from removed videos
        if ($existingModule && !empty($existingVideos)) {
            foreach ($existingVideos as $oldVid) {
                if (!empty($oldVid['file_path']) && !in_array($oldVid['file_path'], $existingFilesKept)) {
                    if (Storage::disk('public')->exists($oldVid['file_path'])) {
                        Storage::disk('public')->delete($oldVid['file_path']);
                    }
                }
            }
        }

        // Fallback for single legacy fields if videos array was empty
        if (empty($recordedVideos) && ($request->filled('recorded_url') || $request->filled('embed_code'))) {
            $recordedVideos[] = [
                'id'         => 'vid_' . uniqid(),
                'title'      => ($request->input('title') ?: 'ক্লাস লেকচার') . ' (রেকর্ডেড ক্লাস)',
                'url'        => $request->input('recorded_url'),
                'embed_code' => $request->input('embed_code'),
                'file_path'  => null,
            ];
        }

        return $recordedVideos;
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

    public function renameFolder(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'old_folder_name' => 'required|string|max:100',
            'new_folder_name' => 'required|string|max:100',
        ]);

        $oldName = trim($validated['old_folder_name']);
        $newName = trim($validated['new_folder_name']);

        $affected = SubjectModule::where('subject_id', $subject->id)
            ->where(function ($q) use ($oldName) {
                if ($oldName === 'General') {
                    $q->whereNull('folder_name')->orWhere('folder_name', '')->orWhere('folder_name', 'General');
                } else {
                    $q->where('folder_name', $oldName);
                }
            })
            ->update(['folder_name' => $newName]);

        return back()->with('success', "ফোল্ডার '{$oldName}' এর নাম পরিবর্তন করে '{$newName}' করা হয়েছে ({$affected}টি মডিউল আপডেট হয়েছে)।");
    }

    public function cloneFolder(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'source_folder_name' => 'required|string|max:100',
            'new_folder_name'    => 'required|string|max:100',
        ]);

        $sourceName = trim($validated['source_folder_name']);
        $newFolderName = trim($validated['new_folder_name']);

        $modules = SubjectModule::where('subject_id', $subject->id)
            ->where(function ($q) use ($sourceName) {
                if ($sourceName === 'General') {
                    $q->whereNull('folder_name')->orWhere('folder_name', '')->orWhere('folder_name', 'General');
                } else {
                    $q->where('folder_name', $sourceName);
                }
            })
            ->orderBy('sequence_no')
            ->get();

        if ($modules->isEmpty()) {
            return back()->with('error', "ফোল্ডার '{$sourceName}' এ কোনো মডিউল পাওয়া যায়নি।");
        }

        $maxSeq = SubjectModule::where('subject_id', $subject->id)->max('sequence_no') ?? 0;

        foreach ($modules as $mod) {
            $newMod = $mod->replicate();
            $newMod->folder_name = $newFolderName;
            $newMod->sequence_no = ++$maxSeq;
            $newMod->save();

            foreach ($mod->learningResources as $lr) {
                $newLr = $lr->replicate();
                $newLr->module_id = $newMod->id;
                $newLr->save();
            }
        }

        return back()->with('success', "ফোল্ডার '{$sourceName}' সফলভাবে কপি করে নতুন ফোল্ডার '{$newFolderName}' তৈরি করা হয়েছে ({$modules->count()}টি মডিউল অন্তর্ভুক্ত)।");
    }

    public function destroy(SubjectModule $module)
    {
        if ($module->file_path && Storage::disk('public')->exists($module->file_path)) {
            Storage::disk('public')->delete($module->file_path);
        }

        if (!empty($module->videos)) {
            foreach ($module->videos as $v) {
                if (!empty($v['file_path']) && Storage::disk('public')->exists($v['file_path'])) {
                    Storage::disk('public')->delete($v['file_path']);
                }
            }
        }

        $module->delete();
        return back()->with('success', 'মডিউল সফলভাবে অপসারণ করা হয়েছে।');
    }
}
