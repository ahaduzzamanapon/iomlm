<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\Course;
use App\Models\Batch;
use App\Models\Semester;

class StudentController extends Controller
{
    /**
     * Build filtered student query based on request parameters
     */
    protected function buildFilteredQuery(Request $request)
    {
        $query = Student::with(['enrollments.batch.course', 'enrollments.semester']);

        // General search term across Name, Code, Phone, Email, NID
        if ($request->filled('search')) {
            $term = trim($request->search);
            $cleanCode = str_replace('-', '', $term);
            $query->where(function ($q) use ($term, $cleanCode) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('student_code', 'like', "%{$term}%")
                  ->orWhere('student_code', 'like', "%{$cleanCode}%")
                  ->orWhere('phone', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('national_id', 'like', "%{$term}%");
            });
        }

        // Specific fields
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . trim($request->name) . '%');
        }

        if ($request->filled('student_code')) {
            $query->where('student_code', 'like', '%' . trim($request->student_code) . '%');
        }

        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . trim($request->phone) . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . trim($request->email) . '%');
        }

        if ($request->filled('gender')) {
            $query->where('gender', strtoupper(trim($request->gender)));
        }

        if ($request->filled('blood_group')) {
            $query->where('blood_group', trim($request->blood_group));
        }

        if ($request->filled('status')) {
            $query->where('status', strtoupper(trim($request->status)));
        }

        // Course, Batch & Semester filters combined on enrollments
        if ($request->filled('course_id') || $request->filled('batch_id') || $request->filled('semester_id')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                if ($request->filled('course_id')) {
                    $courseId = $request->course_id;
                    $q->where(function ($subQ) use ($courseId) {
                        $subQ->where('course_id', $courseId)
                             ->orWhereHas('batch', fn($qb) => $qb->where('course_id', $courseId));
                    });
                }

                if ($request->filled('batch_id')) {
                    $q->where('batch_id', $request->batch_id);
                }

                if ($request->filled('semester_id')) {
                    $q->where('semester_id', $request->semester_id);
                }
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $courses     = Course::where('is_active', true)->orderBy('name')->get();
        $batches     = Batch::with('course')->orderBy('name')->get();
        $semesters   = Semester::with('course')->orderBy('sequence_no')->get();
        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];

        $query = $this->buildFilteredQuery($request);

        $totalCount     = Student::count();
        $activeCount    = Student::where('status', 'ACTIVE')->count();
        $pendingCount   = Student::whereIn('status', ['PENDING', 'LEAD'])->count();
        $graduatedCount = Student::where('status', 'GRADUATED')->count();

        $students = $query->latest()->paginate(25)->appends($request->query());

        $hasFilters = $request->anyFilled([
            'search', 'name', 'student_code', 'phone', 'email',
            'gender', 'blood_group', 'course_id', 'batch_id', 'semester_id',
        ]) || ($request->filled('status') && !in_array($request->status, ['ACTIVE', 'PENDING', 'GRADUATED']));

        $status = $request->query('status');

        return view('admin.students.index', compact(
            'students', 'courses', 'batches', 'semesters', 'bloodGroups',
            'status', 'totalCount', 'activeCount', 'pendingCount', 'graduatedCount', 'hasFilters'
        ));
    }

    /**
     * Export filtered student list to CSV (with UTF-8 BOM for Excel)
     */
    public function exportCsv(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $students = $query->latest()->get();

        $filename = 'students_export_' . now()->format('Y_m_d_His') . '.csv';

        $callback = function () use ($students) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel Bengali font support
            fputs($handle, "\xEF\xBB\xBF");

            // CSV Header Row
            fputcsv($handle, [
                'ক্রমিক (SL)',
                'স্টুডেন্ট আইডি (Student Code)',
                'পূর্ণ নাম (Full Name)',
                'লিঙ্গ (Gender)',
                'মোবাইল নম্বর (Phone)',
                'ইমেইল (Email)',
                'রক্তের গ্রুপ (Blood Group)',
                'এনআইডি / জন্ম নিবন্ধন (NID)',
                'এনরোল্ড কোর্স (Course)',
                'ব্যাচ (Batch)',
                'বর্তমান সেমিস্টার (Semester)',
                'একাডেমিক স্ট্যাটাস (Status)',
                'ভর্তির তারিখ (Registration Date)',
            ]);

            foreach ($students as $index => $st) {
                $enr = $st->enrollments->firstWhere('status', 'ACTIVE') ?? $st->enrollments->first();
                $courseName = $enr?->batch?->course?->title ?? $enr?->batch?->course?->name ?? $enr?->course?->name ?? '—';
                $batchName  = $enr?->batch?->name ?? '—';
                $semName    = $enr?->semester?->name ?? '—';

                $genderLabel = match(strtoupper($st->gender ?? '')) {
                    'MALE'   => 'পুরুষ (Male)',
                    'FEMALE' => 'মহিলা (Female)',
                    'OTHER'  => 'অন্যান্য (Other)',
                    default  => $st->gender ?? '—',
                };

                fputcsv($handle, [
                    $index + 1,
                    $st->student_code ? str_replace('-', '', $st->student_code) : 'N/A',
                    $st->name ?? '',
                    $genderLabel,
                    $st->phone ?? '',
                    $st->email ?? '',
                    $st->blood_group ?? '—',
                    $st->national_id ?? '—',
                    $courseName,
                    $batchName,
                    $semName,
                    $st->status ?? 'ACTIVE',
                    $st->created_at ? $st->created_at->format('d/m/Y') : '—',
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function show(Student $student)
    {
        $student->load([
            'enrollments.batch.course',
            'enrollments.semester',
            'admissions.interestedCourse',
            'invoices',
        ]);
        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:200',
            'phone'         => 'required|string|max:30',
            'email'         => 'nullable|email|unique:students,email,' . $student->id,
            'blood_group'   => 'nullable|string',
            'national_id'   => 'nullable|string|max:50',
            'address'       => 'nullable|string',
            'guardian_name' => 'nullable|string|max:200',
            'guardian_phone'=> 'nullable|string|max:30',
            'status'        => 'required|in:LEAD,PENDING,ACTIVE,ABSENT,DROPPED,CANCELLED,TRANSFERRED,COMPLETED,GRADUATED',
        ]);

        $student->update($validated);

        if (!empty($validated['email']) && $student->user) {
            $student->user->update(['email' => $validated['email']]);
        }

        return redirect()->route('admin.students.show', $student)->with('success', 'Student details updated.');
    }

    public function printGradeSheet(Student $student)
    {
        $student->load(['enrollments.batch.course', 'results.exam.subject', 'attendances']);
        return view('admin.students.grade_sheet', compact('student'));
    }

    public function printCertificate(Student $student)
    {
        $student->load(['enrollments.batch.course']);
        return view('admin.students.certificate', compact('student'));
    }

    public function printIdCard(Student $student)
    {
        $student->load(['enrollments.batch.course']);
        return view('admin.students.id_card', compact('student'));
    }

    /**
     * Impersonate Student (Login directly as student)
     */
    public function impersonate(Student $student)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'অননুমোদিত অনুরোধ। শুধুমাত্র অ্যাডমিন এই সুবিধা ব্যবহার করতে পারবেন।');
        }

        $adminId = Auth::id();

        // Get or create associated User
        $user = $student->user;
        if (!$user) {
            $loginEmail = $student->email ?: ($student->student_code . '@iom.student');
            if (User::where('email', $loginEmail)->exists()) {
                $user = User::where('email', $loginEmail)->first();
            } else {
                $user = User::create([
                    'name'     => $student->name,
                    'email'    => $loginEmail,
                    'password' => Hash::make($student->phone ?: 'iom@1234'),
                    'role'     => 'student',
                ]);
            }
            $student->user_id = $user->id;
            $student->save();
        }

        // Save admin user ID to session
        session()->put('admin_impersonator_id', $adminId);

        // Login as the student
        Auth::login($user);

        return redirect()->route('student.dashboard')
            ->with('success', "আপনি শিক্ষার্থী '{$student->name}' (আইডি: {$student->student_code}) হিসেবে সরাসরি প্রবেশ করেছেন।");
    }

    /**
     * Return back to Admin panel from impersonated student session
     */
    public function leaveImpersonation()
    {
        if (!session()->has('admin_impersonator_id')) {
            return redirect()->route('student.dashboard');
        }

        $adminId = session()->pull('admin_impersonator_id');
        $adminUser = User::find($adminId);

        if ($adminUser && $adminUser->isAdmin()) {
            Auth::login($adminUser);
            return redirect()->route('admin.dashboard')
                ->with('success', 'অ্যাডমিন প্যানেলে সফলভাবে ফিরে এসেছেন।');
        }

        return redirect()->route('login');
    }
}
