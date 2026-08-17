<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
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
        ]);

        $course = Course::create([
            'name'           => $validated['name'],
            'type'           => $validated['type'],
            'duration_value' => $validated['duration_value'],
            'duration_unit'  => $validated['duration_unit'],
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
        $course->load(['semesters', 'courseSubjectMaps.subject', 'courseSubjectMaps.semester']);
        $availableSubjects = Subject::where('is_active', true)->orderBy('name')->get();
        return view('admin.courses.show', compact('course', 'availableSubjects'));
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

    public function destroySemester(Course $course, Semester $semester)
    {
        $semester->delete();
        return back()->with('success', 'Semester removed.');
    }

    public function assignSubject(Request $request, Course $course)
    {
        $validated = $request->validate([
            'subject_id'  => 'required|exists:subjects,id',
            'semester_id' => 'nullable|exists:semesters,id',
        ]);

        // Subject Based কোর্সে ১টির বেশি Subject যুক্ত করা যাবে না
        if ($course->type === 'SUBJECT_BASED' && $course->courseSubjectMaps()->count() >= 1) {
            return back()->with('error', 'Subject Based কোর্সে মাত্র ১টি Subject যুক্ত করা যায়। বিদ্যমান Subject টি আগে রিমুভ করুন।');
        }

        CourseSubjectMap::firstOrCreate([
            'course_id'   => $course->id,
            'subject_id'  => $validated['subject_id'],
            'semester_id' => $course->type === 'SEMESTER_BASED' ? $validated['semester_id'] : null,
        ]);

        return back()->with('success', 'Subject mapped to course successfully.');
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
