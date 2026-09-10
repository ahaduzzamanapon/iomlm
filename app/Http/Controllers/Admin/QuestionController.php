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
                'question_type'     => 'WRITTEN',
                'subject_id'        => $validated['subject_id'] ?? null,
                'question_text'     => $validated['question_text'],
                'difficulty'        => $validated['difficulty'],
                'options'           => [],
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
                        'question_type'     => 'WRITTEN',
                        'subject_id'        => $subject?->id,
                        'difficulty'        => $difficulty,
                        'options'           => [],
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

    /**
     * Download Aiken format demo template (.txt)
     */
    public function downloadAikenTemplate()
    {
        $filename = 'aiken_question_template.txt';
        $headers  = [
            'Content-Type'        => 'text/plain; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $content = "\xEF\xBB\xBF" // UTF-8 BOM
            . "ব্যবস্থাপনার জনক (Father of Modern Management) কাকে বলা হয়?\r\n"
            . "A. হেনরি ফেওল\r\n"
            . "B. এফ. ডব্লিউ. টেলর\r\n"
            . "C. এলটন মেও\r\n"
            . "D. পিটার ড্রাকার\r\n"
            . "ANSWER: A\r\n"
            . "EXPLANATION: আধুনিক ব্যবস্থাপনার জনক হেনরি ফেওল, যিনি ১৪টি মূলনীতি প্রদান করেছিলেন।\r\n\r\n"
            . "পবিত্র কুরআন মাজীদে সর্বমোট কতটি সূরা রয়েছে?\r\n"
            . "A. ১১২টি\r\n"
            . "B. ১১৪টি\r\n"
            . "C. ১১৫টি\r\n"
            . "D. ১২০টি\r\n"
            . "ANSWER: B\r\n\r\n"
            . "What is the capital of Bangladesh?\r\n"
            . "A. Chittagong\r\n"
            . "B. Sylhet\r\n"
            . "C. Dhaka\r\n"
            . "D. Rajshahi\r\n"
            . "ANSWER: C\r\n\r\n"
            . "কম্পিউটারের কেন্দ্রীয় প্রক্রিয়াকরণ অংশ (CPU)-এর মূল অংশ কোনটি?\r\n"
            . "A. ALU (Arithmetic Logic Unit)\r\n"
            . "B. Control Unit\r\n"
            . "C. Memory Unit\r\n"
            . "D. উপরের সবগুলো\r\n"
            . "ANSWER: D\r\n\r\n"
            . "হাদিস শাস্ত্রের বিশুদ্ধতম গ্রন্থ কোনটি?\r\n"
            . "A. সহীহ বুখারী\r\n"
            . "B. সহীহ মুসলিম\r\n"
            . "C. সুনান আন-নাসায়ী\r\n"
            . "D. সুনান আবু দাউদ\r\n"
            . "ANSWER: A\r\n";

        return response($content, 200, $headers);
    }

    /**
     * Bulk upload questions via Aiken format (.txt or direct text)
     */
    public function importAiken(Request $request)
    {
        $request->validate([
            'aiken_file' => 'nullable|file|max:5120',
            'aiken_text' => 'nullable|string',
            'subject_id' => 'nullable|exists:subjects,id',
            'difficulty' => 'nullable|in:easy,medium,hard',
        ]);

        $content = '';
        if ($request->hasFile('aiken_file')) {
            $content = file_get_contents($request->file('aiken_file')->getRealPath());
        } elseif ($request->filled('aiken_text')) {
            $content = $request->input('aiken_text');
        }

        if (empty(trim($content))) {
            return back()->with('error', 'অনুগ্রহ করে একটি Aiken ফরম্যাটের টেক্সট (.txt) ফাইল নির্বাচন করুন অথবা Aiken টেক্সট বক্সে লিখুন।');
        }

        $subjectId  = $request->input('subject_id') ? (int) $request->input('subject_id') : null;
        $difficulty = $request->input('difficulty', 'easy');
        if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
            $difficulty = 'easy';
        }

        $defaultSubject = $subjectId ? Subject::find($subjectId) : null;

        // Parse Aiken text
        $parsed = $this->parseAikenContent($content);

        if (empty($parsed)) {
            return back()->with('error', 'Aiken ফরম্যাটের কোনো প্রশ্ন পাওয়া যায়নি। অনুগ্রহ করে ফরম্যাট যাচাই করুন (প্রতিটি প্রশ্নের নিচে A. B. C. D. এবং শেষে ANSWER: X থাকতে হবে)।');
        }

        $count     = 0;
        $skipped   = 0;
        $duplicate = 0;

        foreach ($parsed as $item) {
            $qText     = trim($item['question_text']);
            $optMap    = $item['options'];
            $ansLetter = strtoupper(trim($item['answer'] ?? ''));

            // Validation: question text, at least 2 options, and valid answer letter
            if (empty($qText) || count($optMap) < 2 || !isset($optMap[$ansLetter])) {
                $skipped++;
                continue;
            }

            // Subject override from metadata if available
            $sub = $defaultSubject;
            if (!empty($item['subject_code'])) {
                $foundSub = Subject::where('code', $item['subject_code'])->orWhere('id', $item['subject_code'])->first();
                if ($foundSub) {
                    $sub = $foundSub;
                }
            }

            // Prepare options list
            $options = [];
            foreach ($optMap as $letter => $optText) {
                $options[] = [
                    'id'   => strtolower($letter),
                    'text' => $optText,
                ];
            }

            $question = Question::firstOrCreate(
                [
                    'question_text' => $qText,
                    'subject_id'    => $sub?->id,
                ],
                [
                    'question_type'     => 'MCQ',
                    'subject_id'        => $sub?->id,
                    'subject_code'      => $sub?->code,
                    'options'           => $options,
                    'correct_option_id' => strtolower($ansLetter),
                    'difficulty'        => $difficulty,
                    'explanation'       => $item['explanation'] ?? null,
                    'is_active'         => true,
                ]
            );

            if ($question->wasRecentlyCreated) {
                $count++;
            } else {
                $duplicate++;
            }
        }

        $msg = "সফলভাবে {$count}টি Aiken ফরম্যাটের প্রশ্ন Import করা হয়েছে!";
        if ($duplicate > 0) {
            $msg .= " ({$duplicate}টি প্রশ্ন পূর্বে থেকেই প্রশ্ন ব্যাংকে ছিল)";
        }
        if ($skipped > 0) {
            $msg .= " ({$skipped}টি অপূর্ণ প্রশ্ন skip করা হয়েছে — অপশন বা উত্তরের ফরম্যাট ভুল ছিল)";
        }

        return back()->with('success', $msg);
    }

    /**
     * Parse raw Aiken format text into structured questions array
     */
    public function parseAikenContent(string $content): array
    {
        // Remove UTF-8 BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        // Normalize line breaks
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines   = explode("\n", $content);

        $parsedQuestions    = [];
        $currentQuestion    = null;
        $currentSubjectCode = null;

        foreach ($lines as $rawLine) {
            $line = trim($rawLine);

            if ($line === '') {
                continue;
            }

            // Metadata: // SUBJECT: BUS101 or [SUBJECT: BUS101]
            if (preg_match('/^(?:\/\/|#|\[)\s*(?:SUBJECT|বিষয়)\s*[:=]\s*([^\]\r\n]+)(?:\])?$/iu', $line, $m)) {
                $currentSubjectCode = trim($m[1]);
                continue;
            }

            // Check for ANSWER line: ANSWER: A or Answer: B or উত্তর: C
            if (preg_match('/^(?:ANSWER|Answer|উত্তর)\s*[:=]\s*([A-Za-z])/u', $line, $m)) {
                if ($currentQuestion && !empty($currentQuestion['options'])) {
                    $currentQuestion['answer'] = strtoupper($m[1]);
                    $parsedQuestions[] = $currentQuestion;
                    $currentQuestion = null;
                }
                continue;
            }

            // Check for EXPLANATION line right after an answer: EXPLANATION: ...
            if (preg_match('/^(?:EXPLANATION|Explanation|ব্যাখ্যা)\s*[:=]\s*(.+)$/iu', $line, $m)) {
                if (!empty($parsedQuestions)) {
                    $lastIdx = count($parsedQuestions) - 1;
                    $parsedQuestions[$lastIdx]['explanation'] = trim($m[1]);
                }
                continue;
            }

            // Check for OPTION line: A. Text or A) Text
            if (preg_match('/^([A-Za-z])[\.\)]\s*(.+)$/u', $line, $m)) {
                $optLetter = strtoupper($m[1]);
                $optText   = trim($m[2]);

                if ($currentQuestion) {
                    $currentQuestion['options'][$optLetter] = $optText;
                }
                continue;
            }

            // If options are already collected or question not started, this starts a new question
            if (!$currentQuestion || !empty($currentQuestion['options'])) {
                $currentQuestion = [
                    'question_text' => $line,
                    'options'       => [],
                    'answer'        => null,
                    'subject_code'  => $currentSubjectCode,
                    'explanation'   => null,
                ];
            } else {
                // Multi-line question statement before first option
                $currentQuestion['question_text'] .= ' ' . $line;
            }
        }

        return $parsedQuestions;
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return back()->with('success', 'প্রশ্নটি মুছে ফেলা হয়েছে।');
    }
}
