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
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'is_international')) {
                $table->boolean('is_international')->default(false)->after('is_expatriate');
            }
        });

        Schema::table('admission_forms', function (Blueprint $table) {
            if (!Schema::hasColumn('admission_forms', 'is_international')) {
                $table->boolean('is_international')->default(false)->after('is_expatriate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'is_international')) {
                $table->dropColumn('is_international');
            }
        });

        Schema::table('admission_forms', function (Blueprint $table) {
            if (Schema::hasColumn('admission_forms', 'is_international')) {
                $table->dropColumn('is_international');
            }
        });
    }
};
