<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\ClassSession;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\SubjectModule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClassSessionController extends Controller
{
    public function index(Request $request)
    {
        $status     = $request->query('status');
        $batchId    = $request->query('batch_id');
        $dateFilter = $request->query('date');
        $type       = $request->query('type'); // 'all', 'extra', 'regular'

        $query = ClassSession::with(['subject', 'batch', 'teacher', 'routineEntry.slot', 'moduleCovered', 'attendances'])
            ->orderBy('session_date', 'desc')
            ->orderBy('start_time', 'asc');

        if ($status)     $query->where('status', $status);
        if ($batchId)    $query->where('batch_id', $batchId);
        if ($dateFilter) $query->whereDate('session_date', $dateFilter);

        if ($type === 'extra') {
            $query->extra();
        } elseif ($type === 'regular') {
            $query->regular();
        }

        $classes  = $query->get();
        $teachers = Teacher::where('is_active', true)->orderBy('name')->get();
        $batches  = Batch::with('course')->orderBy('name')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('admin.classes.index', compact(
            'classes', 'teachers', 'status', 'batches', 'batchId', 'dateFilter', 'type', 'subjects'
        ));
    }

    /**
     * Schedule an Extra Class session (outside routine).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_id'     => 'required|exists:batches,id',
            'subject_id'   => 'required|exists:subjects,id',
            'teacher_id'   => 'nullable|exists:teachers,id',
            'session_date' => 'required|date',
            'start_time'   => 'required|string',
            'end_time'     => 'nullable|string',
            'title'        => 'nullable|string|max:250',
            'reason'       => 'nullable|string|max:250',
            'group_tag'    => 'nullable|in:ALL,MALE,FEMALE,GROUP_A,GROUP_B',
            'meeting_link' => 'nullable|url|max:500',
            'notes'        => 'nullable|string',
        ]);

        $extraSession = ClassSession::create([
            'batch_id'         => $validated['batch_id'],
            'subject_id'       => $validated['subject_id'],
            'teacher_id'       => $validated['teacher_id'] ?? null,
            'session_date'     => $validated['session_date'],
            'start_time'       => $validated['start_time'],
            'end_time'         => $validated['end_time'] ?? null,
            'title'            => $validated['title'] ?? 'বিশেষ এক্সট্রা ক্লাস',
            'reason'           => $validated['reason'] ?? 'রুটিনের অতিরিক্ত ক্লাস',
            'group_tag'        => $validated['group_tag'] ?? 'ALL',
            'meeting_link'     => $validated['meeting_link'] ?? null,
            'notes'            => $validated['notes'] ?? null,
            'is_extra_class'   => true,
            'routine_entry_id' => null,
            'status'           => 'SCHEDULED',
            'teacher_present'  => false,
            'class_conducted'  => false,
        ]);

        return back()->with('success', "রুটিনের বাইরের অতিরিক্ত/এক্সট্রা ক্লাস '{$extraSession->title}' সফলভাবে শিডিউল করা হয়েছে।");
    }

    /**
     * Delete an extra class session.
     */
    public function destroy(ClassSession $class)
    {
        $class->attendances()->delete();
        $class->delete();

        return back()->with('success', 'ক্লাস সেশন সফলভাবে অপসারণ করা হয়েছে।');
    }

    public function show(ClassSession $class)
    {
        $class->load(['subject', 'batch', 'teacher', 'routineEntry.slot', 'moduleCovered', 'attendances.student', 'attendances.enrollment']);

        // All active students enrolled in this batch
        $batchStudents = \App\Models\Enrollment::with('student')
            ->where('batch_id', $class->batch_id)
            ->where('status', 'ACTIVE')
            ->get();

        $teachers = Teacher::where('is_active', true)->orderBy('name')->get();
        $modules  = SubjectModule::where('subject_id', $class->subject_id)->orderBy('sequence_no')->get();

        return view('admin.classes.show', compact('class', 'batchStudents', 'teachers', 'modules'));
    }

    /**
     * Update session date, meeting link, teacher for a specific class session.
     */
    public function updateSchedule(Request $request, ClassSession $class)
    {
        $validated = $request->validate([
            'session_date'      => 'required|date',
            'start_time'        => 'nullable|string',
            'teacher_id'        => 'nullable|exists:teachers,id',
            'meeting_link'      => 'nullable|string|max:500',
            'module_covered_id' => 'nullable|exists:subject_modules,id',
        ]);

        $class->update([
            'session_date'      => $validated['session_date'],
            'start_time'        => $validated['start_time'] ?? $class->start_time,
            'teacher_id'        => $validated['teacher_id'] ?? $class->teacher_id,
            'meeting_link'      => $validated['meeting_link'] ?? $class->meeting_link,
            'module_covered_id' => $validated['module_covered_id'] ?? $class->module_covered_id,
            'status'            => 'SCHEDULED',
        ]);

        return back()->with('success', 'Class session updated successfully.');
    }

    /**
     * Auto-generate a real Zoom Meeting link via Server-to-Server OAuth Zoom API
     */
    public function generateZoomLink(ClassSession $class)
    {
        $class->load(['subject', 'batch']);

        $topic = "{$class->subject->name} ({$class->batch->name})";
        $dateStr = $class->session_date instanceof Carbon ? $class->session_date->format('Y-m-d') : Carbon::parse($class->session_date)->format('Y-m-d');
        $startTime = Carbon::parse($dateStr . ' ' . ($class->start_time ?? '10:00:00'))->toIso8601String();

        try {
            $meetingSvc = new \App\Services\MeetingService();
            $result = $meetingSvc->generate($topic, $startTime);

            if ($result && isset($result['join_url'])) {
                $class->update([
                    'meeting_link' => $result['join_url'],
                    'meeting_id'   => $result['meeting_id'] ?? null,
                    'status'       => 'SCHEDULED',
                ]);
                return back()->with('success', "Real Zoom Meeting generated successfully! Join Link: {$result['join_url']}");
            }

            return back()->with('error', 'Meeting provider is not configured for Zoom in Settings → Meeting Platform.');
        } catch (\Exception $e) {
            return back()->with('error', 'Zoom API Error: ' . $e->getMessage());
        }
    }

    /**
     * Mark a session as completed and save attendance.
     */
    public function markComplete(Request $request, ClassSession $class)
    {
        $request->validate([
            'attendance'        => 'nullable|array',
            'attendance.*'      => 'in:PRESENT,ABSENT,LATE,EXCUSED',
            'module_covered_id' => 'nullable|exists:subject_modules,id',
            'notes'             => 'nullable|string',
        ]);

        $class->update([
            'teacher_present'   => true,
            'class_conducted'   => true,
            'status'            => 'COMPLETED',
            'ended_at'          => now(),
            'module_covered_id' => $request->input('module_covered_id'),
            'notes'             => $request->input('notes'),
        ]);

        foreach ($request->input('attendance', []) as $studentId => $status) {
            $enrollment = \App\Models\Enrollment::where('student_id', $studentId)
                ->where('batch_id', $class->batch_id)
                ->first();
            \App\Models\Attendance::updateOrCreate(
                ['class_session_id' => $class->id, 'student_id' => $studentId],
                ['status' => $status, 'enrollment_id' => $enrollment?->id]
            );
        }

        return back()->with('success', 'Session marked complete. Attendance saved.');
    }

    /**
     * Cancel a session (teacher absent etc.)
     */
    public function markCancelled(Request $request, ClassSession $class)
    {
        $class->update([
            'teacher_present' => false,
            'class_conducted' => false,
            'status'          => 'CANCELLED',
            'notes'           => $request->input('reason', 'Class cancelled'),
        ]);

        return back()->with('success', 'Session marked as cancelled.');
    }

    /**
     * Update attendance records for a class session directly by admin.
     */
    public function updateAttendance(Request $request, ClassSession $class)
    {
        // Support single student quick AJAX update
        if ($request->has('student_id') && $request->has('status')) {
            $request->validate([
                'student_id' => 'required|exists:students,id',
                'status'     => 'required|in:PRESENT,ABSENT,LATE,EXCUSED,NONE',
            ]);

            $studentId = $request->input('student_id');
            $status    = $request->input('status');

            if ($status === 'NONE') {
                \App\Models\Attendance::where('class_session_id', $class->id)
                    ->where('student_id', $studentId)
                    ->delete();
            } else {
                $enrollment = \App\Models\Enrollment::where('student_id', $studentId)
                    ->where('batch_id', $class->batch_id)
                    ->first();

                \App\Models\Attendance::updateOrCreate(
                    ['class_session_id' => $class->id, 'student_id' => $studentId],
                    ['status' => $status, 'enrollment_id' => $enrollment?->id]
                );
            }

            if ($request->expectsJson() || $request->ajax()) {
                $attendances = \App\Models\Attendance::where('class_session_id', $class->id)->get();
                return response()->json([
                    'success' => true,
                    'message' => 'হাজিরা সফলভাবে পরিবর্তন করা হয়েছে।',
                    'stats'   => [
                        'present' => $attendances->where('status', 'PRESENT')->count(),
                        'absent'  => $attendances->where('status', 'ABSENT')->count(),
                        'late'    => $attendances->where('status', 'LATE')->count(),
                        'excused' => $attendances->where('status', 'EXCUSED')->count(),
                        'total'   => $attendances->count(),
                    ],
                ]);
            }

            return back()->with('success', 'হাজিরা সফলভাবে পরিবর্তন করা হয়েছে।');
        }

        // Bulk attendance update (form submit or AJAX bulk)
        $request->validate([
            'attendance'   => 'nullable|array',
            'attendance.*' => 'in:PRESENT,ABSENT,LATE,EXCUSED,NONE',
        ]);

        foreach ($request->input('attendance', []) as $studentId => $status) {
            if ($status === 'NONE' || empty($status)) {
                \App\Models\Attendance::where('class_session_id', $class->id)
                    ->where('student_id', $studentId)
                    ->delete();
            } else {
                $enrollment = \App\Models\Enrollment::where('student_id', $studentId)
                    ->where('batch_id', $class->batch_id)
                    ->first();

                \App\Models\Attendance::updateOrCreate(
                    ['class_session_id' => $class->id, 'student_id' => $studentId],
                    ['status' => $status, 'enrollment_id' => $enrollment?->id]
                );
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            $attendances = \App\Models\Attendance::where('class_session_id', $class->id)->get();
            return response()->json([
                'success' => true,
                'message' => 'সকল শিক্ষার্থীর হাজিরা সফলভাবে সংরক্ষিত হয়েছে।',
                'stats'   => [
                    'present' => $attendances->where('status', 'PRESENT')->count(),
                    'absent'  => $attendances->where('status', 'ABSENT')->count(),
                    'late'    => $attendances->where('status', 'LATE')->count(),
                    'excused' => $attendances->where('status', 'EXCUSED')->count(),
                    'total'   => $attendances->count(),
                ],
            ]);
        }

        return back()->with('success', 'সকল শিক্ষার্থীর হাজিরা সফলভাবে সংরক্ষিত হয়েছে।');
    }

    /**
     * Upload / Update recorded video(s) for a class session.
     */
    public function updateRecording(Request $request, ClassSession $class)
    {
        $request->validate([
            'recording_url'   => 'nullable|string|max:1000',
            'recording_embed' => 'nullable|string',
            'video_file'      => 'nullable|file|mimes:mp4,webm,ogg,mov,avi,mkv|max:512000',
            'videos'          => 'nullable|array',
            'videos.*.title'  => 'nullable|string|max:250',
            'videos.*.url'    => 'nullable|string|max:1000',
            'videos.*.embed_code' => 'nullable|string',
            'videos.*.file'   => 'nullable|file|mimes:mp4,webm,ogg,mov,avi,mkv|max:512000',
            'videos.*.existing_file' => 'nullable|string|max:500',
        ]);

        $recordedVideos = [];

        // 1. Process multiple dynamic video rows if submitted
        if ($request->has('videos') && is_array($request->input('videos'))) {
            foreach ($request->input('videos') as $index => $vidData) {
                $vTitle     = trim($vidData['title'] ?? '');
                $vUrl       = trim($vidData['url'] ?? '');
                $vEmbedCode = trim($vidData['embed_code'] ?? '');
                $vFile      = trim($vidData['existing_file'] ?? '');

                if ($request->hasFile("videos.{$index}.file")) {
                    $uploaded = $request->file("videos.{$index}.file");
                    if ($uploaded && $uploaded->isValid()) {
                        $vFile = $uploaded->store('class_recordings', 'public');
                    }
                }

                if (!empty($vUrl) || !empty($vFile) || !empty($vEmbedCode)) {
                    $recordedVideos[] = [
                        'title'      => !empty($vTitle) ? $vTitle : ('ক্লাস ভিডিও ' . (count($recordedVideos) + 1)),
                        'url'        => !empty($vUrl) ? $vUrl : null,
                        'embed_code' => !empty($vEmbedCode) ? $vEmbedCode : null,
                        'file'       => !empty($vFile) ? $vFile : null,
                    ];
                }
            }
        }

        // 2. Direct single file upload fallback
        if ($request->hasFile('video_file')) {
            $singleFile = $request->file('video_file')->store('class_recordings', 'public');
            $recordedVideos[] = [
                'title'      => 'ক্লাস ভিডিও রেকর্ড',
                'url'        => null,
                'file'       => $singleFile,
                'embed_code' => null,
            ];
        }

        // 3. Direct single URL / Embed fallback if videos array was empty
        if (empty($recordedVideos) && ($request->filled('recording_url') || $request->filled('recording_embed'))) {
            $recordedVideos[] = [
                'title'      => 'ক্লাস ভিডিও রেকর্ড',
                'url'        => $request->input('recording_url'),
                'file'       => null,
                'embed_code' => $request->input('recording_embed'),
            ];
        }

        $firstVideo = $recordedVideos[0] ?? null;

        $class->update([
            'recording_url'   => $firstVideo['url'] ?? null,
            'recording_file'  => $firstVideo['file'] ?? null,
            'recording_embed' => $firstVideo['embed_code'] ?? null,
            'recorded_videos' => !empty($recordedVideos) ? $recordedVideos : null,
            'has_recording'   => !empty($recordedVideos),
        ]);

        return back()->with('success', 'ক্লাস রেকর্ড ভিডিও সফলভাবে সংরক্ষণ ও আপডেট করা হয়েছে।');
    }
}
