<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\FeeHead;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\CourseSubjectMap;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with(['semesters', 'courseSubjectMaps.subject'])->latest()->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        return view('admin.courses.index', compact('courses', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:200',
            'type'           => 'required|in:SUBJECT_BASED,SEMESTER_BASED',
            'duration_value' => 'required|numeric|min:0.5',
            'duration_unit'  => 'required|in:MONTH,YEAR',
            'admission_fee'  => 'nullable|numeric|min:0',
        ]);

        $course = Course::create([
            'name'           => $validated['name'],
            'type'           => $validated['type'],
            'duration_value' => $validated['duration_value'],
            'duration_unit'  => $validated['duration_unit'],
            'admission_fee'  => $validated['admission_fee'] ?? 0.00,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        // Auto-generate semesters if semester-based
        if ($course->type === 'SEMESTER_BASED' && $request->filled('total_semesters')) {
            $total = (int) $request->input('total_semesters');
            for ($i = 1; $i <= $total; $i++) {
                Semester::create([
                    'course_id'   => $course->id,
                    'sequence_no' => $i,
                    'name'        => "Semester {$i}",
                ]);
            }
        }

        return back()->with('success', 'Course created successfully.');
    }

    public function show(Course $course)
    {
        $course->load([
            'semesters',
            'courseSubjectMaps.subject',
            'courseSubjectMaps.semester',
            'feePackages.items.feeHead',
        ]);
        $availableSubjects = Subject::where('is_active', true)->orderBy('name')->get();
        $feeHeads = FeeHead::packageEligible()->orderBy('sort_order')->get();
        return view('admin.courses.show', compact('course', 'availableSubjects', 'feeHeads'));
    }

    public function storeSemester(Request $request, Course $course)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'sequence_no' => 'required|integer|min:1',
        ]);

        Semester::create([
            'course_id'   => $course->id,
            'sequence_no' => $validated['sequence_no'],
            'name'        => $validated['name'],
        ]);

        return back()->with('success', 'Semester added to course.');
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:200',
            'type'           => 'required|in:SUBJECT_BASED,SEMESTER_BASED',
            'duration_value' => 'required|numeric|min:0.5',
            'duration_unit'  => 'required|in:MONTH,YEAR',
            'admission_fee'  => 'nullable|numeric|min:0',
        ]);

        $course->update([
            'name'           => $validated['name'],
            'type'           => $validated['type'],
            'duration_value' => $validated['duration_value'],
            'duration_unit'  => $validated['duration_unit'],
            'admission_fee'  => $validated['admission_fee'] ?? $course->admission_fee,
            'is_active'      => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Course updated successfully.');
    }

    public function destroySemester(Course $course, Semester $semester)
    {
        $semester->delete();
        return back()->with('success', 'Semester removed.');
    }

    public function assignSubject(Request $request, Course $course)
    {
        $request->validate([
            'subject_ids'   => 'required_without:subject_id|array',
            'subject_ids.*' => 'exists:subjects,id',
            'subject_id'    => 'nullable|exists:subjects,id',
            'semester_id'   => 'nullable|exists:semesters,id',
        ]);

        $subjectIds = $request->input('subject_ids');
        if (empty($subjectIds) && $request->filled('subject_id')) {
            $subjectIds = [$request->input('subject_id')];
        }

        // Subject Based কোর্সে ১টির বেশি Subject যুক্ত করা যাবে না
        if ($course->type === 'SUBJECT_BASED' && ($course->courseSubjectMaps()->count() + count($subjectIds)) > 1) {
            return back()->with('error', 'Subject Based কোর্সে মাত্র ১টি Subject যুক্ত করা যায়। বিদ্যমান Subject টি আগে রিমুভ করুন।');
        }

        foreach ($subjectIds as $subId) {
            CourseSubjectMap::firstOrCreate([
                'course_id'   => $course->id,
                'subject_id'  => $subId,
                'semester_id' => $course->type === 'SEMESTER_BASED' ? $request->input('semester_id') : null,
            ]);
        }

        return back()->with('success', 'Subjects mapped to course successfully.');
    }

    public function removeSubject(Course $course, CourseSubjectMap $map)
    {
        $map->delete();
        return back()->with('success', 'Subject removed from course.');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'Course deleted.');
    }
}
