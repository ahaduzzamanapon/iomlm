<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_answers', function (Blueprint $table) {
            $table->text('answer_image_path')->nullable()->after('marks_awarded'); // Written answer image
            $table->decimal('teacher_marks', 6, 2)->nullable()->after('answer_image_path'); // Teacher graded marks
            $table->unsignedBigInteger('graded_by')->nullable()->after('teacher_marks'); // who graded
        });
    }

    public function down(): void
    {
        Schema::table('exam_answers', function (Blueprint $table) {
            $table->dropColumn(['answer_image_path', 'teacher_marks', 'graded_by']);
        });
    }
};
