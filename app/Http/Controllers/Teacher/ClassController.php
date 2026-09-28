<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Teacher;
use App\Models\SubjectModule;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Services\MeetingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClassController extends Controller
{
    private function teacher(): ?Teacher
    {
        return Teacher::where('user_id', auth()->id())->first();
    }

    /**
     * All sessions for this teacher, ordered by date desc.
     */
    public function index()
    {
        $teacher = $this->teacher();
        $meetingProvider = (new MeetingService())->provider();
        $today = Carbon::today()->toDateString();

        if (!$teacher) {
            $sessions = collect();
            $batches = collect();
            $subjects = collect();
            return view('teacher.classes.index', compact('sessions', 'today', 'meetingProvider', 'batches', 'subjects'));
        }

        $sessions = ClassSession::with(['subject', 'batch', 'routineEntry.slot', 'moduleCovered', 'attendances', 'teacher'])
            ->where('teacher_id', $teacher->id)
            ->orderBy('session_date', 'desc')
            ->get()
            ->groupBy(fn($s) => $s->session_date?->format('Y-W'));

        $assignedSubjectIds = \App\Models\SubjectTeacherAssignment::where('teacher_id', $teacher->id)->pluck('subject_id');
        $subjects = \App\Models\Subject::where('is_active', true)
            ->where(function($q) use ($assignedSubjectIds) {
                if ($assignedSubjectIds->isNotEmpty()) {
                    $q->whereIn('id', $assignedSubjectIds);
                }
            })
            ->orderBy('name')
            ->get();
        $batches = \App\Models\Batch::where('status', 'ACTIVE')->orderBy('name')->get();

        return view('teacher.classes.index', compact('sessions', 'today', 'meetingProvider', 'batches', 'subjects'));
    }

    /**
     * Today's classes for this teacher.
     */
    public function today()
    {
        $teacher = $this->teacher();
        $today   = Carbon::today();
        $meetingProvider = (new MeetingService())->provider();

        if (!$teacher) {
            $sessions = collect();
            return view('teacher.classes.today', compact('sessions', 'today', 'meetingProvider'));
        }

        $sessions = ClassSession::with(['subject', 'batch', 'routineEntry.slot', 'moduleCovered', 'teacher'])
            ->where('teacher_id', $teacher->id)
            ->where('session_date', $today->toDateString())
            ->orderBy('start_time')
            ->get();

        return view('teacher.classes.today', compact('sessions', 'today', 'meetingProvider'));
    }

    private function authorizeSession(ClassSession $class): ?Teacher
    {
        $teacher = $this->teacher();
        if (!$teacher || ($class->teacher_id && $class->teacher_id !== $teacher->id)) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Unauthorized access to this class session.');
            }
        }
        return $teacher;
    }

    /**
     * Conduct a specific session: add meeting link, log module, take attendance.
     */
    public function conduct(ClassSession $class)
    {
        $this->authorizeSession($class);
        $class->load(['subject', 'batch', 'routineEntry.slot', 'teacher', 'attendances.student', 'moduleCovered']);
        $meetingProvider = (new MeetingService())->provider();

        $batchStudentsQuery = Enrollment::with('student')
            ->where('batch_id', $class->batch_id)
            ->where('status', 'ACTIVE');

        if ($class->group_tag === 'MALE') {
            $batchStudentsQuery->whereHas('student', fn($q) => $q->where('gender', 'MALE'));
        } elseif ($class->group_tag === 'FEMALE') {
            $batchStudentsQuery->whereHas('student', fn($q) => $q->where('gender', 'FEMALE'));
        } elseif (!empty($class->group_tag) && $class->group_tag !== 'ALL') {
            $batchStudentsQuery->where('group_tag', $class->group_tag);
        }

        $batchStudents = $batchStudentsQuery->get();

        $modules = SubjectModule::where('subject_id', $class->subject_id)
            ->orderBy('sequence_no')
            ->get();

        return view('teacher.classes.conduct', compact('class', 'batchStudents', 'modules', 'meetingProvider'));
    }

    /**
     * Teacher sets meeting link (and optionally a custom date/time) for a session.
     */
    public function setLink(Request $request, ClassSession $class)
    {
        $this->authorizeSession($class);

        $validated = $request->validate([
            'session_date' => 'nullable|date',
            'start_time'   => 'nullable|string',
            'meeting_link' => 'nullable|url|max:500',
        ]);

        $link      = $validated['meeting_link'] ?? null;
        $meetingId = null;

        // If no link provided, try to auto-generate via configured provider
        if (!$link) {
            $meetingSvc = new MeetingService();
            $provider   = $meetingSvc->provider();

            if ($provider === 'zoom') {
                // Auto-generate via Zoom API
                $sessionDate = $validated['session_date'] ?? ($class->session_date ? Carbon::parse($class->session_date)->toDateString() : null);
                $startTime   = $validated['start_time']   ?? $class->start_time;
                $isoStart    = $sessionDate && $startTime
                    ? Carbon::parse("{$sessionDate} {$startTime}")->toIso8601String()
                    : Carbon::now()->addHour()->toIso8601String();

                try {
                    $topic  = ($class->subject?->name ?? 'Class') . ' — ' . ($class->batch?->name ?? '');
                    $result = $meetingSvc->generate($topic, $isoStart);
                    if ($result) {
                        $link      = $result['join_url'];
                        $meetingId = $result['meeting_id'];
                    }
                } catch (\RuntimeException $e) {
                    return back()->with('error', $e->getMessage());
                }
            } else {
                // Google Meet / Manual — teacher must paste link
                $providerLabel = $provider === 'google_meet' ? 'Google Meet' : 'meeting';
                return back()->with('error',
                    "Please paste your {$providerLabel} link in the form. Auto-generation is only available with Zoom."
                );
            }
        }

        // Parse meeting ID from URL if manually pasted (Zoom URLs usually contain /j/MEETING_ID)
        if (!$meetingId && $link && str_contains($link, 'zoom.us')) {
            if (preg_match('/\/j\/(\d+)/', $link, $matches)) {
                $meetingId = $matches[1];
            }
        }

        $class->update([
            'session_date'    => $validated['session_date'] ?? $class->session_date,
            'start_time'      => $validated['start_time']   ?? $class->start_time,
            'meeting_link'    => $link,
            'zoom_meeting_id' => $meetingId ?? $class->zoom_meeting_id,
            'status'          => 'SCHEDULED',
        ]);

        return back()->with('success', 'Meeting link saved successfully.');
    }

    /**
     * Auto-sync attendance directly from Zoom Participants report.
     */
    public function syncZoomAttendance(ClassSession $class)
    {
        if (!$class->zoom_meeting_id) {
            // Try extracting from URL if exists
            if ($class->meeting_link && preg_match('/\/j\/(\d+)/', $class->meeting_link, $m)) {
                $class->update(['zoom_meeting_id' => $m[1]]);
            } else {
                return back()->with('error', 'This class session does not have a valid Zoom Meeting ID.');
            }
        }

        $meetingSvc = new MeetingService();

        try {
            $participants = $meetingSvc->getZoomParticipants((string) $class->zoom_meeting_id);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (empty($participants)) {
            return back()->with('error', 'No participant record found from Zoom. Ensure the meeting has ended.');
        }

        // Map participant emails and names for case-insensitive matching
        $joinedEmails = collect($participants)->pluck('user_email')->filter()->map(fn($e) => strtolower(trim($e)))->toArray();
        $joinedNames  = collect($participants)->pluck('name')->filter()->map(fn($n) => strtolower(trim($n)))->toArray();

        $enrolledStudentsQuery = Enrollment::with('student')
            ->where('batch_id', $class->batch_id)
            ->where('status', 'ACTIVE');

        if ($class->group_tag === 'MALE') {
            $enrolledStudentsQuery->whereHas('student', fn($q) => $q->where('gender', 'MALE'));
        } elseif ($class->group_tag === 'FEMALE') {
            $enrolledStudentsQuery->whereHas('student', fn($q) => $q->where('gender', 'FEMALE'));
        } elseif (!empty($class->group_tag) && $class->group_tag !== 'ALL') {
            $enrolledStudentsQuery->where('group_tag', $class->group_tag);
        }

        $enrolledStudents = $enrolledStudentsQuery->get();

        $markedPresentCount = 0;

        foreach ($enrolledStudents as $enr) {
            $student     = $enr->student;
            $stdEmail    = strtolower(trim($student->email ?? ''));
            $stdName     = strtolower(trim($student->name ?? ''));

            $isPresent = false;

            if ($stdEmail && in_array($stdEmail, $joinedEmails)) {
                $isPresent = true;
            } elseif ($stdName) {
                // Partial name match if exact email wasn't found
                foreach ($joinedNames as $jName) {
                    if (str_contains($jName, $stdName) || str_contains($stdName, $jName)) {
                        $isPresent = true;
                        break;
                    }
                }
            }

            $status = $isPresent ? 'PRESENT' : 'ABSENT';

            Attendance::updateOrCreate(
                ['class_session_id' => $class->id, 'student_id' => $student->id],
                [
                    'status'        => $status,
                    'enrollment_id' => $enr->id,
                ]
            );

            if ($isPresent) {
                $markedPresentCount++;
            }
        }

        // Mark class session as conducted
        $class->update([
            'class_conducted' => true,
            'teacher_present' => true,
            'status'          => 'COMPLETED',
            'ended_at'        => now(),
        ]);

        return back()->with('success', "⚡ Auto Attendance Synced from Zoom! {$markedPresentCount} out of {$enrolledStudents->count()} students marked PRESENT.");
    }

    /**
     * Mark session complete + save attendance + log module covered.
     */
    public function markComplete(Request $request, ClassSession $class)
    {
        $request->validate([
            'attendance'        => 'nullable|array',
            'attendance.*'      => 'in:PRESENT,ABSENT,LATE,EXCUSED',
            'module_covered_id' => 'nullable|exists:subject_modules,id',
            'notes'             => 'nullable|string',
        ]);

        $updateData = [
            'teacher_present'   => true,
            'class_conducted'   => true,
            'status'            => 'COMPLETED',
            'ended_at'          => now(),
            'module_covered_id' => $request->input('module_covered_id'),
            'notes'             => $request->input('notes'),
        ];

        // Process optional recording data if submitted
        if ($request->has('videos') && is_array($request->input('videos'))) {
            $submittedVideos = [];
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
                    $submittedVideos[] = [
                        'title'      => !empty($vTitle) ? $vTitle : ('ক্লাস ভিডিও ' . (count($submittedVideos) + 1)),
                        'url'        => !empty($vUrl) ? $vUrl : null,
                        'embed_code' => !empty($vEmbedCode) ? $vEmbedCode : null,
                        'file'       => !empty($vFile) ? $vFile : null,
                    ];
                }
            }
            if (!empty($submittedVideos)) {
                $first = $submittedVideos[0];
                $updateData['recording_url']   = $first['url'] ?? null;
                $updateData['recording_file']  = $first['file'] ?? null;
                $updateData['recording_embed'] = $first['embed_code'] ?? null;
                $updateData['recorded_videos'] = $submittedVideos;
                $updateData['has_recording']   = true;
            }
        }

        $class->update($updateData);

        foreach ($request->input('attendance', []) as $studentId => $status) {
            Attendance::updateOrCreate(
                ['class_session_id' => $class->id, 'student_id' => $studentId],
                ['status' => $status]
            );
        }

        return redirect()->route('teacher.classes.index')
            ->with('success', 'Class completed. Attendance saved!');
    }

    /**
     * Cancel session (teacher absent, etc.)
     */
    public function markCancelled(Request $request, ClassSession $class)
    {
        $this->authorizeSession($class);

        $class->update([
            'teacher_present' => false,
            'class_conducted' => false,
            'status'          => 'CANCELLED',
            'notes'           => $request->input('reason', 'Class cancelled'),
        ]);

        return redirect()->route('teacher.classes.index')
            ->with('success', 'Session marked as cancelled.');
    }

    /**
     * Teacher's calendar view — sessions as calendar events.
     */
    public function calendar()
    {
        $teacher = $this->teacher();

        if (!$teacher) {
            $events   = collect();
            $sessions = collect();
            return view('teacher.calendar.index', compact('events', 'sessions'));
        }

        $sessions = ClassSession::with(['subject', 'batch', 'routineEntry.slot', 'teacher'])
            ->where('teacher_id', $teacher->id)
            ->whereNotNull('session_date')
            ->get();

        $events = $sessions->map(fn($s) => [
            'id'           => $s->id,
            'title'        => ($s->subject?->name ?? '—') . ' — ' . ($s->batch?->name ?? ''),
            'subject_name' => $s->subject?->name ?? '—',
            'batch_name'   => $s->batch?->name ?? '—',
            'slot_name'    => $s->routineEntry?->slot?->name ?? '',
            'date'         => $s->session_date?->toDateString(),
            'start_time'   => $s->start_time ? \Carbon\Carbon::parse($s->start_time)->format('h:i A') : 'TBA',
            'meeting_link' => $s->meeting_link,
            'status'       => $s->status,
        ]);

        return view('teacher.calendar.index', compact('events', 'sessions'));
    }

    /**
     * Weekly schedule view (alias for index).
     */
     public function schedule()
     {
         return $this->index();
     }

    /**
     * Teacher schedule an extra class session.
     */
    public function storeExtra(Request $request)
    {
        $teacher = $this->teacher();
        if (!$teacher) {
            return back()->with('error', 'অনুমোদিত শিক্ষক পাওয়া যায়নি।');
        }

        $validated = $request->validate([
            'batch_id'     => 'required|exists:batches,id',
            'subject_id'   => 'required|exists:subjects,id',
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
            'teacher_id'       => $teacher->id,
            'session_date'     => $validated['session_date'],
            'start_time'       => $validated['start_time'],
            'end_time'         => $validated['end_time'] ?? null,
            'title'            => $validated['title'] ?? 'শিক্ষক কর্তৃক এক্সট্রা ক্লাস',
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

        return back()->with('success', "আপনার এক্সট্রা ক্লাস '{$extraSession->title}' সফলভাবে শিডিউল করা হয়েছে।");
    }

    /**
     * Teacher upload / update recorded video(s) for a class session.
     */
    public function updateRecording(Request $request, ClassSession $class)
    {
        $this->authorizeSession($class);

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

        if ($request->hasFile('video_file')) {
            $singleFile = $request->file('video_file')->store('class_recordings', 'public');
            $recordedVideos[] = [
                'title'      => 'ক্লাস ভিডিও রেকর্ড',
                'url'        => null,
                'file'       => $singleFile,
                'embed_code' => null,
            ];
        }

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

        return back()->with('success', 'ক্লাস রেকর্ড ভিডিও সফলভাবে সংরক্ষিত ও আপডেট করা হয়েছে।');
    }
}
