<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\AdmissionForm;
use App\Models\Batch;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Session-wise Admission Summary Report (ভর্তি সামারি রিপোর্ট)
     */
    public function index(Request $request)
    {
        // 1. All Academic Sessions for Dropdown
        $sessions = AcademicSession::with('academicYear')
            ->orderByDesc('id')
            ->get();

        // 2. Resolve Selected Session (defaults to current active session)
        $selectedSession = null;
        if ($request->filled('session_id')) {
            $selectedSession = $sessions->firstWhere('id', (int) $request->query('session_id'));
        }
        if (!$selectedSession) {
            $selectedSession = $sessions->firstWhere('is_active', true) ?: $sessions->first();
        }

        // 3. Admission Date Filter (defaults to today)
        $admissionDate = $request->query('admission_date', today()->toDateString());
        try {
            $carbonDate = Carbon::parse($admissionDate);
            $formattedDateHeader = $carbonDate->format('d.m.y');
        } catch (\Throwable $e) {
            $carbonDate = today();
            $admissionDate = $carbonDate->toDateString();
            $formattedDateHeader = $carbonDate->format('d.m.y');
        }

        // 4. Faculty Filter
        $selectedFaculty = $request->query('faculty', 'All');
        $faculties = Course::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        // 5. Status Filter (defaults to APPROVED, can be ALL)
        $status = $request->query('status', 'APPROVED');

        // 6. Courses query
        $coursesQuery = Course::where('is_active', true);
        if ($selectedFaculty !== 'All' && !empty($selectedFaculty)) {
            $coursesQuery->where('department', $selectedFaculty);
        }
        $courses = $coursesQuery->orderBy('name')->get();

        // 7. Aggregate Admission Statistics for the selected session
        $stats = collect();
        $incompleteProfilesCount = 0;

        if ($selectedSession) {
            $statsQuery = AdmissionForm::query()
                ->leftJoin('students', 'admission_forms.student_id', '=', 'students.id')
                ->leftJoin('batches', 'admission_forms.batch_id', '=', 'batches.id')
                ->where('admission_forms.academic_session_id', $selectedSession->id);

            if ($status !== 'ALL') {
                $statsQuery->whereIn('admission_forms.status', ['APPROVED', 'ENROLLED']);
            }

            $stats = $statsQuery
                ->selectRaw("COALESCE(admission_forms.interested_course_id, batches.course_id) as course_id")
                ->selectRaw("COUNT(CASE WHEN COALESCE(admission_forms.gender, students.gender) = 'Male' THEN 1 END) as male_count")
                ->selectRaw("COUNT(CASE WHEN COALESCE(admission_forms.gender, students.gender) = 'Female' THEN 1 END) as female_count")
                ->selectRaw("COUNT(admission_forms.id) as total_count")
                ->selectRaw("COUNT(CASE WHEN (DATE(admission_forms.reviewed_at) = ? OR (admission_forms.reviewed_at IS NULL AND DATE(admission_forms.created_at) = ?)) THEN 1 END) as date_count", [$admissionDate, $admissionDate])
                ->groupByRaw("COALESCE(admission_forms.interested_course_id, batches.course_id)")
                ->get()
                ->keyBy('course_id');

            // Incomplete Profiles Count in this session
            $incompleteProfilesCount = AdmissionForm::where('academic_session_id', $selectedSession->id)
                ->whereHas('student', function ($q) {
                    $q->where('profile_completed_percent', '<', 95)
                      ->orWhereNull('profile_completed_percent');
                })
                ->count();
        }

        // 8. Calculate Totals
        $subtotalMale = 0;
        $subtotalFemale = 0;
        $subtotalTotal = 0;
        $subtotalDate = 0;

        foreach ($courses as $c) {
            $s = $stats->get($c->id);
            if ($s) {
                $subtotalMale += (int) $s->male_count;
                $subtotalFemale += (int) $s->female_count;
                $subtotalTotal += (int) $s->total_count;
                $subtotalDate += (int) $s->date_count;
            }
        }

        return view('admin.reports.index', compact(
            'sessions',
            'selectedSession',
            'admissionDate',
            'formattedDateHeader',
            'faculties',
            'selectedFaculty',
            'status',
            'courses',
            'stats',
            'incompleteProfilesCount',
            'subtotalMale',
            'subtotalFemale',
            'subtotalTotal',
            'subtotalDate'
        ));
    }

    /**
     * Export Session Admission Summary as CSV
     */
    public function exportAdmissionSummary(Request $request): StreamedResponse
    {
        $sessionId = $request->query('session_id');
        $session = AcademicSession::with('academicYear')->find($sessionId) ?: AcademicSession::with('academicYear')->where('is_active', true)->first();
        $admissionDate = $request->query('admission_date', today()->toDateString());
        $selectedFaculty = $request->query('faculty', 'All');
        $status = $request->query('status', 'APPROVED');

        $coursesQuery = Course::where('is_active', true);
        if ($selectedFaculty !== 'All' && !empty($selectedFaculty)) {
            $coursesQuery->where('department', $selectedFaculty);
        }
        $courses = $coursesQuery->orderBy('name')->get();

        $stats = collect();
        if ($session) {
            $statsQuery = AdmissionForm::query()
                ->leftJoin('students', 'admission_forms.student_id', '=', 'students.id')
                ->leftJoin('batches', 'admission_forms.batch_id', '=', 'batches.id')
                ->where('admission_forms.academic_session_id', $session->id);

            if ($status !== 'ALL') {
                $statsQuery->whereIn('admission_forms.status', ['APPROVED', 'ENROLLED']);
            }

            $stats = $statsQuery
                ->selectRaw("COALESCE(admission_forms.interested_course_id, batches.course_id) as course_id")
                ->selectRaw("COUNT(CASE WHEN COALESCE(admission_forms.gender, students.gender) = 'Male' THEN 1 END) as male_count")
                ->selectRaw("COUNT(CASE WHEN COALESCE(admission_forms.gender, students.gender) = 'Female' THEN 1 END) as female_count")
                ->selectRaw("COUNT(admission_forms.id) as total_count")
                ->selectRaw("COUNT(CASE WHEN (DATE(admission_forms.reviewed_at) = ? OR (admission_forms.reviewed_at IS NULL AND DATE(admission_forms.created_at) = ?)) THEN 1 END) as date_count", [$admissionDate, $admissionDate])
                ->groupByRaw("COALESCE(admission_forms.interested_course_id, batches.course_id)")
                ->get()
                ->keyBy('course_id');
        }

        $sessionYear = $session?->academicYear?->name ? '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $session->academicYear->name) : '';
        $sessionNameClean = $session ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $session->name) : 'admission_report';
        $filename = "admission_report_{$sessionNameClean}{$sessionYear}_{$admissionDate}.csv";

        return response()->streamDownload(function () use ($courses, $stats, $admissionDate) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, ["Program's Name", "Male", "Female", "Grand Total", "Admissions on {$admissionDate}", "Remark"]);

            $totMale = 0;
            $totFemale = 0;
            $totGrand = 0;
            $totDate = 0;

            foreach ($courses as $c) {
                $st = $stats->get($c->id);
                $m = $st ? (int) $st->male_count : 0;
                $f = $st ? (int) $st->female_count : 0;
                $tot = $st ? (int) $st->total_count : 0;
                $d = $st ? (int) $st->date_count : 0;

                $totMale += $m;
                $totFemale += $f;
                $totGrand += $tot;
                $totDate += $d;

                fputcsv($handle, [$c->name, $m, $f, $tot, $d, '']);
            }

            fputcsv($handle, ["Grand Total", $totMale, $totFemale, $totGrand, $totDate, '']);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * System Analytics & KPI Dashboard
     */
    public function systemAnalytics(Request $request)
    {
        $stats = [
            'active_students'   => Student::where('status', 'ACTIVE')->count(),
            'pending_leads'     => Student::where('status', 'PENDING')->count(),
            'active_teachers'   => Teacher::where('is_active', true)->count(),
            'total_courses'     => Course::where('is_active', true)->count(),
            'active_batches'    => Batch::where('status', 'ACTIVE')->count(),
            'completed_classes' => ClassSession::where('status', 'COMPLETED')->count(),
            'total_exams'       => Exam::count(),
        ];

        return view('admin.reports.system', compact('stats'));
    }
}
