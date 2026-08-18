<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $search    = $request->query('search');
        $subjectId = $request->query('subject_id');
        $typeFilter = $request->query('type'); // MCQ or WRITTEN

        $query = Question::with('subject')->latest();

        if ($search) {
            $query->where('question_text', 'like', "%{$search}%");
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($typeFilter) {
            $query->where('question_type', strtoupper($typeFilter));
        }

        $questions = $query->paginate(20)->withQueryString();
        $subjects  = Subject::where('is_active', true)->orderBy('name')->get();

        return view('admin.questions.index', compact('questions', 'subjects', 'search', 'subjectId', 'typeFilter'));
    }

    public function store(Request $request)
    {
        $type = strtoupper($request->input('question_type', 'MCQ'));

        if ($type === 'MCQ') {
            $validated = $request->validate([
                'subject_id'        => 'nullable|exists:subjects,id',
                'question_text'     => 'required|string',
                'option_a'          => 'required|string',
                'option_b'          => 'required|string',
                'option_c'          => 'required|string',
                'option_d'          => 'required|string',
                'correct_option_id' => 'required|in:a,b,c,d',
                'difficulty'        => 'required|in:easy,medium,hard',
            ]);

            $options = [
                ['id' => 'a', 'text' => $validated['option_a']],
                ['id' => 'b', 'text' => $validated['option_b']],
                ['id' => 'c', 'text' => $validated['option_c']],
                ['id' => 'd', 'text' => $validated['option_d']],
            ];

            Question::create([
                'question_type'     => 'MCQ',
                'subject_id'        => $validated['subject_id'] ?? null,
                'question_text'     => $validated['question_text'],
                'options'           => $options,
                'correct_option_id' => $validated['correct_option_id'],
                'difficulty'        => $validated['difficulty'],
            ]);
        } else {
            $validated = $request->validate([
                'subject_id'    => 'nullable|exists:subjects,id',
                'question_text' => 'required|string',
                'difficulty'    => 'required|in:easy,medium,hard',
            ]);

            Question::create([
                'question_type' => 'WRITTEN',
                'subject_id'    => $validated['subject_id'] ?? null,
                'question_text' => $validated['question_text'],
                'difficulty'    => $validated['difficulty'],
                'options'       => null,
                'correct_option_id' => null,
            ]);
        }

        return back()->with('success', 'নতুন প্রশ্ন সফলভাবে যুক্ত হয়েছে।');
    }

    /**
     * Bulk upload via CSV file
     * CSV columns: question_type, subject_code, question_text, option_a, option_b, option_c, option_d, correct_option, difficulty
     */
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'nullable|file|mimes:csv,txt',
        ]);

        if (!$request->hasFile('csv_file')) {
            return back()->with('error', 'অনুগ্রহ করে একটি CSV ফাইল নির্বাচন করুন।');
        }

        $path = $request->file('csv_file')->getRealPath();
        $file = fopen($path, 'r');

        // Skip header row
        $headers = fgetcsv($file);
        if (!$headers) {
            return back()->with('error', 'CSV ফাইল ফাঁকা অথবা ফরম্যাট ভুল।');
        }

        // Normalize header keys
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        $count = 0;
        $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 3) {
                $skipped++;
                continue;
            }

            $data = array_combine($headers, array_pad($row, count($headers), ''));

            $questionText = trim($data['question_text'] ?? '');
            $type = strtoupper(trim($data['question_type'] ?? 'MCQ'));

            if (!$questionText) {
                $skipped++;
                continue;
            }

            // Find subject by code
            $subject = null;
            $subjectCode = trim($data['subject_code'] ?? '');
            if ($subjectCode) {
                $subject = Subject::where('code', $subjectCode)->orWhere('id', $subjectCode)->first();
            }

            $difficulty = strtolower(trim($data['difficulty'] ?? 'easy'));
            if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
                $difficulty = 'easy';
            }

            if ($type === 'WRITTEN') {
                Question::firstOrCreate(
                    ['question_text' => $questionText],
                    [
                        'question_type' => 'WRITTEN',
                        'subject_id'    => $subject?->id,
                        'difficulty'    => $difficulty,
                        'options'       => null,
                        'correct_option_id' => null,
                    ]
                );
            } else {
                $optA = trim($data['option_a'] ?? '');
                $optB = trim($data['option_b'] ?? '');
                $optC = trim($data['option_c'] ?? '');
                $optD = trim($data['option_d'] ?? '');
                $correct = strtolower(trim($data['correct_option'] ?? 'a'));

                if (!$optA || !$optB || !$optC || !$optD || !in_array($correct, ['a','b','c','d'])) {
                    $skipped++;
                    continue;
                }

                $options = [
                    ['id' => 'a', 'text' => $optA],
                    ['id' => 'b', 'text' => $optB],
                    ['id' => 'c', 'text' => $optC],
                    ['id' => 'd', 'text' => $optD],
                ];

                Question::firstOrCreate(
                    ['question_text' => $questionText],
                    [
                        'question_type'     => 'MCQ',
                        'subject_id'        => $subject?->id,
                        'options'           => $options,
                        'correct_option_id' => $correct,
                        'difficulty'        => $difficulty,
                    ]
                );
            }

            $count++;
        }

        fclose($file);

        $msg = "সফলভাবে {$count}টি প্রশ্ন import করা হয়েছে!";
        if ($skipped) {
            $msg .= " ({$skipped}টি row skip হয়েছে — ফরম্যাট ভুল বা ডুপ্লিকেট)";
        }

        return back()->with('success', $msg);
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $filename = 'question_bank_template.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fputs($file, "\xEF\xBB\xBF");
            // Header row
            fputcsv($file, [
                'question_type', 'subject_code', 'question_text',
                'option_a', 'option_b', 'option_c', 'option_d',
                'correct_option', 'difficulty',
            ]);
            // MCQ example
            fputcsv($file, [
                'MCQ', 'BUS101',
                'ব্যবস্থাপনার জনক (Father of Modern Management) কাকে বলা হয়?',
                'হেনরি ফেওল', 'এফ. ডব্লিউ. টেলর', 'এলটন মেও', 'পিটার ড্রাকার',
                'a', 'easy',
            ]);
            // Written example
            fputcsv($file, [
                'WRITTEN', 'BUS101',
                'ব্যবস্থাপনার প্রকৃতি ও বৈশিষ্ট্য সম্পর্কে আলোচনা করো।',
                '', '', '', '', '', 'medium',
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return back()->with('success', 'প্রশ্নটি মুছে ফেলা হয়েছে।');
    }
}
