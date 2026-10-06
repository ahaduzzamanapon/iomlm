<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Course;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        EmailTemplate::seedDefaultTemplates();

        $query = EmailTemplate::with(['course', 'batch', 'creator']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('subject', 'like', "%{$s}%")
                  ->orWhere('content', 'like', "%{$s}%");
            });
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->filled('gender') && $request->gender !== 'ALL_GENDERS') {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('category') && $request->category !== 'ALL_CATEGORIES') {
            $query->where('category', $request->category);
        }

        $templates = $query->orderBy('category')
            ->orderBy('is_system')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $courses = Course::where('is_active', true)->orderBy('name')->get();
        $batches = Batch::where('status', 'ACTIVE')->orderBy('name')->get();

        return view('admin.email_templates.index', compact('templates', 'courses', 'batches'));
    }

    public function create(Request $request)
    {
        $courses = Course::where('is_active', true)->with(['batches' => function ($q) {
            $q->where('status', 'ACTIVE');
        }])->orderBy('name')->get();

        $batches = Batch::where('status', 'ACTIVE')->orderBy('name')->get();

        return view('admin.email_templates.create', compact('courses', 'batches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:190',
            'category'   => 'required|string|in:EXAM,FEES,CLASS,HOLIDAY,ADMISSION,GENERAL',
            'course_id'  => 'nullable|exists:courses,id',
            'batch_id'   => 'nullable|exists:batches,id',
            'gender'     => 'nullable|in:Male,Female,All',
            'subject'    => 'nullable|string|max:255',
            'content'    => 'required|string',
            'is_active'  => 'nullable|boolean',
        ]);

        $template = EmailTemplate::create([
            'name'       => $validated['name'],
            'category'   => $validated['category'],
            'course_id'  => $validated['course_id'] ?? null,
            'batch_id'   => $validated['batch_id'] ?? null,
            'gender'     => $validated['gender'] ?? 'All',
            'subject'    => $validated['subject'] ?? null,
            'content'    => $validated['content'],
            'is_system'  => false,
            'is_active'  => $request->has('is_active') ? $request->boolean('is_active') : true,
            'created_by' => auth()->id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'নতুন ইমেইল টেমপ্লেট সফলভাবে সংরক্ষিত হয়েছে!',
                'template' => $template->load(['course', 'batch']),
            ]);
        }

        return redirect()->route('admin.email-templates.index')
            ->with('success', "নতুন ইমেইল টেমপ্লেট '{$template->name}' সফলভাবে সংরক্ষিত হয়েছে!");
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        $courses = Course::where('is_active', true)->with(['batches' => function ($q) {
            $q->where('status', 'ACTIVE');
        }])->orderBy('name')->get();

        $batches = Batch::where('status', 'ACTIVE')->orderBy('name')->get();

        return view('admin.email_templates.edit', compact('emailTemplate', 'courses', 'batches'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:190',
            'category'   => 'required|string|in:EXAM,FEES,CLASS,HOLIDAY,ADMISSION,GENERAL',
            'course_id'  => 'nullable|exists:courses,id',
            'batch_id'   => 'nullable|exists:batches,id',
            'gender'     => 'nullable|in:Male,Female,All',
            'subject'    => 'nullable|string|max:255',
            'content'    => 'required|string',
            'is_active'  => 'nullable|boolean',
        ]);

        $emailTemplate->update([
            'name'       => $validated['name'],
            'category'   => $validated['category'],
            'course_id'  => $validated['course_id'] ?? null,
            'batch_id'   => $validated['batch_id'] ?? null,
            'gender'     => $validated['gender'] ?? 'All',
            'subject'    => $validated['subject'] ?? null,
            'content'    => $validated['content'],
            'is_active'  => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'ইমেইল টেমপ্লেট সফলভাবে আপডেট করা হয়েছে!',
                'template' => $emailTemplate->load(['course', 'batch']),
            ]);
        }

        return redirect()->route('admin.email-templates.index')
            ->with('success', "ইমেইল টেমপ্লেট '{$emailTemplate->name}' সফলভাবে আপডেট করা হয়েছে!");
    }

    public function destroy(EmailTemplate $emailTemplate, Request $request)
    {
        if ($emailTemplate->is_system) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'সিস্টেমের ডিফল্ট টেমপ্লেট মুছে ফেলা যাবে না।'], 422);
            }
            return back()->with('error', 'সিস্টেমের ডিফল্ট টেমপ্লেট মুছে ফেলা যাবে না।');
        }

        $emailTemplate->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'টেমপ্লেট সফলভাবে মুছে ফেলা হয়েছে!']);
        }

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'টেমপ্লেট সফলভাবে মুছে ফেলা হয়েছে!');
    }

    public function listJson(Request $request)
    {
        EmailTemplate::seedDefaultTemplates();

        $query = EmailTemplate::with(['course:id,name,code', 'batch:id,name,batch_code'])
            ->where('is_active', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('course_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('course_id', $request->course_id)->orWhereNull('course_id');
            });
        }

        if ($request->filled('batch_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('batch_id', $request->batch_id)->orWhereNull('batch_id');
            });
        }

        if ($request->filled('gender')) {
            $query->where(function ($q) use ($request) {
                $q->where('gender', $request->gender)->orWhere('gender', 'All');
            });
        }

        $templates = $query->orderBy('is_system', 'desc')->orderBy('name')->get();

        return response()->json([
            'success'   => true,
            'templates' => $templates,
        ]);
    }
}
