<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Invoice;

return new class extends Migration
{
    public function up(): void
    {
        // Fix SEMESTER invoice titles for SUBJECT_BASED courses:
        // "Semester Tuition Fee" → "Course Tuition Fee"
        // Remove "(Current Semester)" suffix
        $invs = Invoice::where('category', 'SEMESTER')
            ->where('title', 'like', '%Semester Tuition Fee%')
            ->get();

        foreach ($invs as $inv) {
            $enrollment = $inv->enrollment;
            if (!$enrollment) continue;
            $course = $enrollment->course;
            if (!$course || $course->type !== 'SUBJECT_BASED') continue;

            $newTitle = str_replace('Semester Tuition Fee', 'Course Tuition Fee', $inv->title);
            $newTitle = preg_replace('/\s*\(Current Semester\)\s*/i', '', $newTitle);
            $inv->update(['title' => trim($newTitle)]);
        }
    }

    public function down(): void {}
};
