<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'permanent_address')) {
                $table->text('permanent_address')->nullable()->after('address');
            }
            if (!Schema::hasColumn('students', 'occupation')) {
                $table->string('occupation', 100)->nullable()->after('gender');
            }
            if (!Schema::hasColumn('students', 'education_qualification')) {
                $table->string('education_qualification', 100)->nullable()->after('occupation');
            }
            if (!Schema::hasColumn('students', 'ssc_school')) {
                $table->string('ssc_school', 200)->nullable()->after('education_qualification');
            }
            if (!Schema::hasColumn('students', 'hsc_college')) {
                $table->string('hsc_college', 200)->nullable()->after('ssc_year');
            }
            if (!Schema::hasColumn('students', 'university_name')) {
                $table->string('university_name', 200)->nullable()->after('hsc_year');
            }
            if (!Schema::hasColumn('students', 'department_name')) {
                $table->string('department_name', 100)->nullable()->after('university_name');
            }
            if (!Schema::hasColumn('students', 'nationality')) {
                $table->string('nationality', 50)->nullable()->default('Bangladeshi')->after('national_id');
            }
            if (!Schema::hasColumn('students', 'religion')) {
                $table->string('religion', 50)->nullable()->default('Islam')->after('nationality');
            }
            if (!Schema::hasColumn('students', 'profile_completed_percent')) {
                $table->unsignedTinyInteger('profile_completed_percent')->default(20)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $cols = [
                'permanent_address', 'occupation', 'education_qualification',
                'ssc_school', 'hsc_college', 'university_name', 'department_name',
                'nationality', 'religion', 'profile_completed_percent'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('students', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
