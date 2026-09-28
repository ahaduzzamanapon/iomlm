<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_submissions', 'shuffled_options')) {
                $table->json('shuffled_options')->nullable()->after('assigned_question_ids');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('exam_submissions', 'shuffled_options')) {
                $table->dropColumn('shuffled_options');
            }
        });
    }
};
