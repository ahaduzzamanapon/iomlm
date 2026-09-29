<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:190',
            'category' => 'required|string|in:EXAM,FEES,CLASS,HOLIDAY,ADMISSION,GENERAL',
            'subject'  => 'nullable|string|max:255',
            'content'  => 'required|string',
        ]);

        $template = EmailTemplate::create([
            'name'       => $validated['name'],
            'category'   => $validated['category'],
            'subject'    => $validated['subject'] ?? null,
            'content'    => $validated['content'],
            'is_system'  => false,
            'created_by' => auth()->id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'নতুন ইমেইল টেমপ্লেট সফলভাবে সংরক্ষিত হয়েছে!',
                'template' => $template,
            ]);
        }

        return back()->with('success', 'নতুন ইমেইল টেমপ্লেট সফলভাবে সংরক্ষিত হয়েছে!');
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

        return back()->with('success', 'টেমপ্লেট সফলভাবে মুছে ফেলা হয়েছে!');
    }

    public function listJson()
    {
        EmailTemplate::seedDefaultTemplates();
        $templates = EmailTemplate::orderBy('is_system', 'desc')->orderBy('name')->get();
        return response()->json(['success' => true, 'templates' => $templates]);
    }
}
