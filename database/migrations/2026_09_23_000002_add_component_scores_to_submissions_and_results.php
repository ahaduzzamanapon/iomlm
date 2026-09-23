<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('exam_submissions')) {
            Schema::table('exam_submissions', function (Blueprint $table) {
                if (!Schema::hasColumn('exam_submissions', 'mcq_score')) {
                    $table->decimal('mcq_score', 8, 2)->nullable()->default(0.00)->after('total_score');
                }
                if (!Schema::hasColumn('exam_submissions', 'written_score')) {
                    $table->decimal('written_score', 8, 2)->nullable()->default(0.00)->after('mcq_score');
                }
                if (!Schema::hasColumn('exam_submissions', 'tamrin_score')) {
                    $table->decimal('tamrin_score', 8, 2)->nullable()->default(0.00)->after('written_score');
                }
                if (!Schema::hasColumn('exam_submissions', 'viva_score')) {
                    $table->decimal('viva_score', 8, 2)->nullable()->default(0.00)->after('tamrin_score');
                }
            });
        }

        if (Schema::hasTable('results')) {
            Schema::table('results', function (Blueprint $table) {
                if (!Schema::hasColumn('results', 'mcq_marks')) {
                    $table->decimal('mcq_marks', 8, 2)->nullable()->after('marks');
                }
                if (!Schema::hasColumn('results', 'written_marks')) {
                    $table->decimal('written_marks', 8, 2)->nullable()->after('mcq_marks');
                }
                if (!Schema::hasColumn('results', 'tamrin_marks')) {
                    $table->decimal('tamrin_marks', 8, 2)->nullable()->after('written_marks');
                }
                if (!Schema::hasColumn('results', 'viva_marks')) {
                    $table->decimal('viva_marks', 8, 2)->nullable()->after('tamrin_marks');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('exam_submissions')) {
            Schema::table('exam_submissions', function (Blueprint $table) {
                $cols = ['mcq_score', 'written_score', 'tamrin_score', 'viva_score'];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('exam_submissions', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }

        if (Schema::hasTable('results')) {
            Schema::table('results', function (Blueprint $table) {
                $cols = ['mcq_marks', 'written_marks', 'tamrin_marks', 'viva_marks'];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('results', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }
    }
};
