<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_submissions', function (Blueprint $table) {
            $table->json('assigned_question_ids')->nullable()->after('student_id');
        });
    }

    public function down(): void
    {
        Schema::table('exam_submissions', function (Blueprint $table) {
            $table->dropColumn('assigned_question_ids');
        });
    }
};
