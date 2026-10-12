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
            if (!Schema::hasColumn('students', 'is_expatriate')) {
                $table->boolean('is_expatriate')->default(false)->after('permanent_address');
            }
            if (!Schema::hasColumn('students', 'country_of_residence')) {
                $table->string('country_of_residence', 100)->nullable()->after('is_expatriate');
            }
            if (!Schema::hasColumn('students', 'present_division_id')) {
                $table->foreignId('present_division_id')->nullable()->after('country_of_residence')
                      ->constrained('divisions')->nullOnDelete();
            }
            if (!Schema::hasColumn('students', 'present_district_id')) {
                $table->foreignId('present_district_id')->nullable()->after('present_division_id')
                      ->constrained('districts')->nullOnDelete();
            }
            if (!Schema::hasColumn('students', 'permanent_division_id')) {
                $table->foreignId('permanent_division_id')->nullable()->after('present_district_id')
                      ->constrained('divisions')->nullOnDelete();
            }
            if (!Schema::hasColumn('students', 'permanent_district_id')) {
                $table->foreignId('permanent_district_id')->nullable()->after('permanent_division_id')
                      ->constrained('districts')->nullOnDelete();
            }
        });

        Schema::table('admission_forms', function (Blueprint $table) {
            if (!Schema::hasColumn('admission_forms', 'is_expatriate')) {
                $table->boolean('is_expatriate')->default(false)->after('nationality');
            }
            if (!Schema::hasColumn('admission_forms', 'country_of_residence')) {
                $table->string('country_of_residence', 100)->nullable()->after('is_expatriate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission_forms', function (Blueprint $table) {
            $cols = ['is_expatriate', 'country_of_residence'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('admission_forms', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('students', function (Blueprint $table) {
            $foreignKeys = [
                'students_present_division_id_foreign',
                'students_present_district_id_foreign',
                'students_permanent_division_id_foreign',
                'students_permanent_district_id_foreign',
            ];
            foreach ($foreignKeys as $fk) {
                try {
                    $table->dropForeign($fk);
                } catch (\Throwable $e) {
                    // Ignore if FK doesn't exist
                }
            }

            $cols = [
                'is_expatriate', 'country_of_residence',
                'present_division_id', 'present_district_id',
                'permanent_division_id', 'permanent_district_id'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('students', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
