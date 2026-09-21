<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SubjectModule;
use App\Models\SubjectCategory;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $categoryId = $request->query('category_id');
        $search     = $request->query('search');

        $query = Subject::with('category')->withCount('modules');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $subjects   = $query->latest()->get();
        $categories = SubjectCategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.subjects.index', compact('subjects', 'categories', 'categoryId', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:subject_categories,id',
            'name'        => 'required|string|max:200',
            'code'        => 'required|string|max:30|unique:subjects,code',
            'credit'      => 'required|integer|min:1|max:10',
            'full_marks'  => 'required|integer|min:10',
            'pass_marks'  => 'required|integer|min:1',
        ]);

        Subject::create([
            'category_id' => $validated['category_id'] ?? null,
            'name'        => $validated['name'],
            'code'        => strtoupper($validated['code']),
            'credit'      => $validated['credit'],
            'full_marks'  => $validated['full_marks'],
            'pass_marks'  => $validated['pass_marks'],
            'version'     => 1,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'বিষয় সফলভাবে তৈরি করা হয়েছে।');
    }

    public function show(Subject $subject)
    {
        $subject->load(['category', 'modules' => fn($q) => $q->orderBy('sequence_no')]);
        return view('admin.subjects.show', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:subject_categories,id',
            'name'        => 'required|string|max:200',
            'code'        => 'required|string|max:30|unique:subjects,code,' . $subject->id,
            'credit'      => 'required|integer|min:1|max:10',
            'full_marks'  => 'required|integer|min:10',
            'pass_marks'  => 'required|integer|min:1',
        ]);

        $subject->update([
            'category_id' => $validated['category_id'] ?? null,
            'name'        => $validated['name'],
            'code'        => strtoupper($validated['code']),
            'credit'      => $validated['credit'],
            'full_marks'  => $validated['full_marks'],
            'pass_marks'  => $validated['pass_marks'],
            'is_active'   => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'বিষয় সফলভাবে আপডেট করা হয়েছে।');
    }

    public function clone(Request $request, Subject $subject)
    {
        $request->validate([
            'new_code' => 'nullable|string|max:30|unique:subjects,code',
            'new_name' => 'nullable|string|max:200',
        ]);

        $cloned = $subject->cloneSubject(
            $request->filled('new_code') ? strtoupper($request->input('new_code')) : null,
            $request->input('new_name')
        );

        return back()->with('success', "বিষয় '{$subject->name}' এবং এর সকল মডিউল সফলভাবে ক্লোন করা হয়েছে (নতুন কোড: {$cloned->code})।");
    }

    public function destroy(Subject $subject)
    {
        $reasons = [];

        $examsCount = \App\Models\Exam::where('subject_id', $subject->id)->count();
        if ($examsCount > 0) {
            $reasons[] = "{$examsCount}টি পরীক্ষার সাথে যুক্ত";
        }

        $routineCount = \App\Models\RoutineEntry::where('subject_id', $subject->id)->count();
        if ($routineCount > 0) {
            $reasons[] = "{$routineCount}টি ক্লাস রুটিন এন্ট্রির সাথে যুক্ত";
        }

        $finalMarksCount = \Illuminate\Support\Facades\DB::table('final_marks')->where('subject_id', $subject->id)->count();
        if ($finalMarksCount > 0) {
            $reasons[] = "শিক্ষার্থীদের পরীক্ষার ফলাফলের রেকর্ডের সাথে যুক্ত";
        }

        $classSessionsCount = \App\Models\ClassSession::where('subject_id', $subject->id)->count();
        if ($classSessionsCount > 0) {
            $reasons[] = "{$classSessionsCount}টি লাইভ ক্লাস সেশনের সাথে যুক্ত";
        }

        $retakesCount = \Illuminate\Support\Facades\DB::table('subject_retakes')->where('subject_id', $subject->id)->count();
        if ($retakesCount > 0) {
            $reasons[] = "শিক্ষার্থীদের রিটেক আবেদনের সাথে যুক্ত";
        }

        $questionsCount = \App\Models\Question::where('subject_id', $subject->id)->count();
        if ($questionsCount > 0) {
            $reasons[] = "প্রশ্নব্যাংকে {$questionsCount}টি প্রশ্নের সাথে যুক্ত";
        }

        if (!empty($reasons)) {
            $reasonText = implode(', ', $reasons);
            return back()->with('error', "এই বিষয়টি ডিলিট করা যাবে না কারণ এটি: {$reasonText}। আপনি চাইলে বিষয়টির স্ট্যাটাস নিষ্ক্রিয় (Inactive) করে রাখতে পারেন।");
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($subject) {
            \App\Models\CourseSubjectMap::where('subject_id', $subject->id)->delete();
            \App\Models\SubjectTeacherAssignment::where('subject_id', $subject->id)->delete();
            \App\Models\SubjectModule::where('subject_id', $subject->id)->delete();
            \Illuminate\Support\Facades\DB::table('timelines')->where('subject_id', $subject->id)->delete();
            $subject->delete();
        });

        return redirect()->route('admin.subjects.index')->with('success', 'বিষয়টি সফলভাবে মুছে ফেলা হয়েছে।');
    }
}
