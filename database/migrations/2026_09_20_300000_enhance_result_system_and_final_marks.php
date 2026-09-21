<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enhance final_marks table
        if (Schema::hasTable('final_marks')) {
            Schema::table('final_marks', function (Blueprint $table) {
                if (!Schema::hasColumn('final_marks', 'tamrin_mark')) {
                    $table->decimal('tamrin_mark', 5, 2)->nullable()->after('attendance_converted');
                }
                if (!Schema::hasColumn('final_marks', 'tajweed_mark')) {
                    $table->decimal('tajweed_mark', 5, 2)->nullable()->after('tamrin_mark');
                }
                if (!Schema::hasColumn('final_marks', 'dns_mark')) {
                    $table->decimal('dns_mark', 5, 2)->nullable()->after('tajweed_mark');
                }
                if (!Schema::hasColumn('final_marks', 'merit_position')) {
                    $table->unsignedInteger('merit_position')->nullable()->after('status');
                }
                if (!Schema::hasColumn('final_marks', 'is_published')) {
                    $table->boolean('is_published')->default(false)->after('merit_position');
                }
                if (!Schema::hasColumn('final_marks', 'published_at')) {
                    $table->timestamp('published_at')->nullable()->after('is_published');
                }
            });
        }

        // 2. Enhance exams table
        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                if (!Schema::hasColumn('exams', 'is_result_published')) {
                    $table->boolean('is_result_published')->default(false)->after('is_anti_cheating');
                }
                if (!Schema::hasColumn('exams', 'result_published_at')) {
                    $table->timestamp('result_published_at')->nullable()->after('is_result_published');
                }
            });
        }

        // 3. Enhance results table
        if (Schema::hasTable('results')) {
            Schema::table('results', function (Blueprint $table) {
                if (!Schema::hasColumn('results', 'is_published')) {
                    $table->boolean('is_published')->default(false)->after('is_final_counted');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('results') && Schema::hasColumn('results', 'is_published')) {
            Schema::table('results', function (Blueprint $table) {
                $table->dropColumn('is_published');
            });
        }

        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                if (Schema::hasColumn('exams', 'is_result_published')) {
                    $table->dropColumn('is_result_published');
                }
                if (Schema::hasColumn('exams', 'result_published_at')) {
                    $table->dropColumn('result_published_at');
                }
            });
        }

        if (Schema::hasTable('final_marks')) {
            Schema::table('final_marks', function (Blueprint $table) {
                $cols = ['tamrin_mark', 'tajweed_mark', 'dns_mark', 'merit_position', 'is_published', 'published_at'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('final_marks', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
