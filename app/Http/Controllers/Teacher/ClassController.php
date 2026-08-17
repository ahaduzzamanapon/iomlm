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
        return Teacher::where('email', auth()->user()->email)->first();
    }

    /**
     * All sessions for this teacher, ordered by date desc.
     */
    public function index()
    {
        $teacher = $this->teacher();
        $meetingProvider = (new MeetingService())->provider();

        $sessions = ClassSession::with(['subject', 'batch', 'routineEntry.slot', 'moduleCovered', 'attendances'])
            ->where('teacher_id', $teacher?->id)
            ->orderBy('session_date', 'desc')
            ->get()
            ->groupBy(fn($s) => $s->session_date?->format('Y-W'));

        $today = Carbon::today()->toDateString();

        return view('teacher.classes.index', compact('sessions', 'today', 'meetingProvider'));
    }

    /**
     * Today's classes for this teacher.
     */
    public function today()
    {
        $teacher = $this->teacher();
        $today   = Carbon::today();
        $meetingProvider = (new MeetingService())->provider();

        $sessions = ClassSession::with(['subject', 'batch', 'routineEntry.slot', 'moduleCovered'])
            ->where('teacher_id', $teacher?->id)
            ->where('session_date', $today->toDateString())
            ->orderBy('start_time')
            ->get();

        return view('teacher.classes.today', compact('sessions', 'today', 'meetingProvider'));
    }

    /**
     * Conduct a specific session: add meeting link, log module, take attendance.
     */
    public function conduct(ClassSession $class)
    {
        $class->load(['subject', 'batch', 'routineEntry.slot', 'teacher', 'attendances.student', 'moduleCovered']);
        $meetingProvider = (new MeetingService())->provider();

        $batchStudents = Enrollment::with('student')
            ->where('batch_id', $class->batch_id)
            ->where('status', 'ACTIVE')
            ->get();

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
                $sessionDate = $validated['session_date'] ?? $class->session_date?->toDateString();
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

        $enrolledStudents = Enrollment::with('student')
            ->where('batch_id', $class->batch_id)
            ->where('status', 'ACTIVE')
            ->get();

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

        $class->update([
            'teacher_present'   => true,
            'class_conducted'   => true,
            'status'            => 'COMPLETED',
            'ended_at'          => now(),
            'module_covered_id' => $request->input('module_covered_id'),
            'notes'             => $request->input('notes'),
        ]);

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

        $sessions = ClassSession::with(['subject', 'batch', 'routineEntry.slot'])
            ->where('teacher_id', $teacher?->id)
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
}
