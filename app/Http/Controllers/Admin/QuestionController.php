<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $search    = $request->query('search');
        $subjectId = $request->query('subject_id');

        $query = Question::with('subject')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('question_text', 'like', "%{$search}%")
                  ->orWhere('explanation', 'like', "%{$search}%");
            });
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        $questions = $query->paginate(20)->withQueryString();
        $subjects  = Subject::where('is_active', true)->orderBy('name')->get();

        return view('admin.questions.index', compact('questions', 'subjects', 'search', 'subjectId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id'        => 'nullable|exists:subjects,id',
            'subject_code'      => 'nullable|string|max:100',
            'question_text'     => 'required|string',
            'option_a'          => 'required|string',
            'option_b'          => 'required|string',
            'option_c'          => 'required|string',
            'option_d'          => 'required|string',
            'correct_option_id' => 'required|in:a,b,c,d',
            'explanation'       => 'nullable|string',
            'difficulty'        => 'required|in:easy,medium,hard',
        ]);

        $options = [
            ['id' => 'a', 'text' => $validated['option_a']],
            ['id' => 'b', 'text' => $validated['option_b']],
            ['id' => 'c', 'text' => $validated['option_c']],
            ['id' => 'd', 'text' => $validated['option_d']],
        ];

        Question::create([
            'subject_id'        => $validated['subject_id'] ?? null,
            'subject_code'      => $validated['subject_code'] ?? null,
            'question_text'     => $validated['question_text'],
            'options'           => $options,
            'correct_option_id' => $validated['correct_option_id'],
            'explanation'       => $validated['explanation'] ?? null,
            'difficulty'        => $validated['difficulty'],
        ]);

        return back()->with('success', 'নতুন প্রশ্ন সফলভাবে যুক্ত হয়েছে।');
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'json_file' => 'nullable|file|mimes:json,txt',
            'json_text' => 'nullable|string',
        ]);

        $jsonContent = null;

        if ($request->hasFile('json_file')) {
            $jsonContent = file_get_contents($request->file('json_file')->getRealPath());
        } elseif ($request->filled('json_text')) {
            $jsonContent = $request->input('json_text');
        }

        if (!$jsonContent) {
            return back()->with('error', 'অনুগ্রহ করে একটি JSON ফাইল নির্বাচন করুন অথবা JSON কোড পেস্ট করুন।');
        }

        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', 'অবৈধ JSON ফরম্যাট: ' . json_last_error_msg());
        }

        // Handle single object or array of objects
        $questionsList = isset($data[0]) ? $data : [$data];
        $count = 0;

        foreach ($questionsList as $item) {
            if (!isset($item['question_text']) || !isset($item['options']) || !isset($item['correct_option_id'])) {
                continue;
            }

            // Match subject if subject_id is code/id
            $subject = null;
            if (isset($item['subject_id'])) {
                $subject = Subject::where('id', $item['subject_id'])
                    ->orWhere('code', $item['subject_id'])
                    ->first();
            }

            Question::firstOrCreate(
                ['question_text' => $item['question_text']],
                [
                    'subject_id'        => $subject?->id,
                    'subject_code'      => $item['subject_id'] ?? null,
                    'options'           => $item['options'],
                    'correct_option_id' => strtolower($item['correct_option_id']),
                    'explanation'       => $item['explanation'] ?? null,
                    'difficulty'        => strtolower($item['difficulty'] ?? 'easy'),
                ]
            );

            $count++;
        }

        return back()->with('success', "সফলভাবে {$count}টি প্রশ্ন ইমপোর্ট করা হয়েছে!");
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return back()->with('success', 'প্রশ্নটি মুছে ফেলা হয়েছে।');
    }
}
