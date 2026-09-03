<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Question;
use App\Models\Subject;

$subject = Subject::first();
$subjectId = $subject ? $subject->id : null;

echo "Using Subject ID: " . ($subjectId ?? 'None (Global)') . "\n";

$questions = [
    // MCQ Question 1
    [
        'question_type'     => 'MCQ',
        'subject_id'        => $subjectId,
        'question_text'     => 'ইসলামী শরীয়াহর প্রধান উৎস কোনটি?',
        'options'           => [
            ['id' => 'a', 'text' => 'আল-কুরআন'],
            ['id' => 'b', 'text' => 'সুন্নাহ'],
            ['id' => 'c', 'text' => 'ইজমা'],
            ['id' => 'd', 'text' => 'কিয়াস'],
        ],
        'correct_option_id' => 'a',
        'difficulty'        => 'easy',
    ],
    // MCQ Question 2
    [
        'question_type'     => 'MCQ',
        'subject_id'        => $subjectId,
        'question_text'     => 'হাদিস সংকলনে ইমাম বুখারী (র:) রচিত সবচেয়ে নির্ভরযোগ্য গ্রন্থের নাম কী?',
        'options'           => [
            ['id' => 'a', 'text' => 'সহিহ মুসলিম'],
            ['id' => 'b', 'text' => 'সহিহ বুখারী'],
            ['id' => 'c', 'text' => 'সুনানে আবু দাউদ'],
            ['id' => 'd', 'text' => 'জামিউত তিরমিজী'],
        ],
        'correct_option_id' => 'b',
        'difficulty'        => 'medium',
    ],
    // Written Question 1
    [
        'question_type'     => 'WRITTEN',
        'subject_id'        => $subjectId,
        'question_text'     => 'ইলমে ফিকহ্ এর সংজ্ঞা ও প্রয়োজনীয়তা বিস্তারিতভাবে আলোচনা করো।',
        'options'           => [],
        'correct_option_id' => null,
        'difficulty'        => 'medium',
    ],
    // Written Question 2
    [
        'question_type'     => 'WRITTEN',
        'subject_id'        => $subjectId,
        'question_text'     => 'তাওহীদ, রিসালাত ও আখেরাতের মৌলিক বিশ্বাসগুলো কুরআন ও সুন্নাহর আলোকে বর্ণনা করো।',
        'options'           => [],
        'correct_option_id' => null,
        'difficulty'        => 'hard',
    ],
];

foreach ($questions as $qData) {
    $q = Question::create($qData);
    echo "Added Question ID #{$q->id} ({$q->question_type}): {$q->question_text}\n";
}

echo "Successfully added sample questions!\n";
