<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Removes hyphens/dashes from all student codes to merge them into a single string.
     */
    public function up(): void
    {
        if (Schema::hasTable('students') && Schema::hasColumn('students', 'student_code')) {
            DB::statement("UPDATE students SET student_code = REPLACE(student_code, '-', '') WHERE student_code IS NOT NULL AND student_code LIKE '%-%'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: cannot reliably re-insert arbitrary hyphens
    }
};
