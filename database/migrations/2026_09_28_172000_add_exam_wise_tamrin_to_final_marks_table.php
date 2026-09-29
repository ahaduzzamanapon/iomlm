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
        if (Schema::hasTable('final_marks')) {
            Schema::table('final_marks', function (Blueprint $table) {
                if (!Schema::hasColumn('final_marks', 'ct_tamrin')) {
                    $table->decimal('ct_tamrin', 5, 2)->nullable()->after('class_test_converted')->comment('Class Test Exam Tamrin mark');
                }
                if (!Schema::hasColumn('final_marks', 'midterm_tamrin')) {
                    $table->decimal('midterm_tamrin', 5, 2)->nullable()->after('midterm_converted')->comment('Midterm Exam Tamrin mark');
                }
                if (!Schema::hasColumn('final_marks', 'final_tamrin')) {
                    $table->decimal('final_tamrin', 5, 2)->nullable()->after('final_converted')->comment('Final Exam Tamrin mark');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('final_marks')) {
            Schema::table('final_marks', function (Blueprint $table) {
                $cols = ['ct_tamrin', 'midterm_tamrin', 'final_tamrin'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('final_marks', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
