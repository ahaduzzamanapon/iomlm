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
        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                if (!Schema::hasColumn('exams', 'has_mcq')) {
                    $table->boolean('has_mcq')->default(true)->after('negative_marking');
                }
                if (!Schema::hasColumn('exams', 'mcq_marks')) {
                    $table->decimal('mcq_marks', 8, 2)->default(0.00)->after('has_mcq');
                }
                if (!Schema::hasColumn('exams', 'has_written')) {
                    $table->boolean('has_written')->default(false)->after('mcq_marks');
                }
                if (!Schema::hasColumn('exams', 'written_marks')) {
                    $table->decimal('written_marks', 8, 2)->default(0.00)->after('has_written');
                }
                if (!Schema::hasColumn('exams', 'has_tamrin')) {
                    $table->boolean('has_tamrin')->default(false)->after('written_marks');
                }
                if (!Schema::hasColumn('exams', 'tamrin_marks')) {
                    $table->decimal('tamrin_marks', 8, 2)->default(0.00)->after('has_tamrin');
                }
                if (!Schema::hasColumn('exams', 'has_viva')) {
                    $table->boolean('has_viva')->default(false)->after('tamrin_marks');
                }
                if (!Schema::hasColumn('exams', 'viva_marks')) {
                    $table->decimal('viva_marks', 8, 2)->default(0.00)->after('has_viva');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                $cols = [
                    'has_mcq', 'mcq_marks',
                    'has_written', 'written_marks',
                    'has_tamrin', 'tamrin_marks',
                    'has_viva', 'viva_marks',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('exams', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
