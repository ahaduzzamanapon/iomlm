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
        Schema::table('admission_forms', function (Blueprint $table) {
            if (!Schema::hasColumn('admission_forms', 'gender')) {
                $table->string('gender', 20)->nullable()->after('student_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission_forms', function (Blueprint $table) {
            if (Schema::hasColumn('admission_forms', 'gender')) {
                $table->dropColumn('gender');
            }
        });
    }
};
