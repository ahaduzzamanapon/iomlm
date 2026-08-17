<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_forms', function (Blueprint $table) {
            // Source: admin-created or public form
            $table->enum('source', ['ADMIN', 'PUBLIC'])->default('ADMIN')->after('id');
            $table->string('application_no', 30)->nullable()->unique()->after('source');

            // Academic session (for public form)
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete()->after('interested_course_id');

            // Step 2: Education & Job Info
            $table->string('occupation', 100)->nullable()->after('academic_session_id');
            $table->string('education_qualification', 100)->nullable()->after('occupation');
            $table->string('ssc_school', 200)->nullable()->after('education_qualification');
            $table->string('ssc_board', 100)->nullable()->after('ssc_school');
            $table->unsignedSmallInteger('ssc_year')->nullable()->after('ssc_board');
            $table->string('hsc_college', 200)->nullable()->after('ssc_year');
            $table->string('hsc_board', 100)->nullable()->after('hsc_college');
            $table->unsignedSmallInteger('hsc_year')->nullable()->after('hsc_board');
            $table->string('university_name', 200)->nullable()->after('hsc_year');
            $table->string('department_name', 100)->nullable()->after('university_name');
            $table->string('device_type', 50)->nullable()->after('department_name');

            // Step 3: Personal Info
            $table->foreignId('blood_group_id')->nullable()->constrained('blood_groups')->nullOnDelete()->after('device_type');
            $table->string('passport_no', 50)->nullable()->after('blood_group_id');
            $table->string('birth_certificate_no', 50)->nullable()->after('passport_no');
            $table->string('nationality', 50)->nullable()->default('Bangladeshi')->after('birth_certificate_no');
            $table->foreignId('religion_id')->nullable()->constrained('religions')->nullOnDelete()->after('nationality');

            // Present Address
            $table->string('present_house', 300)->nullable()->after('religion_id');
            $table->string('present_post_office', 100)->nullable()->after('present_house');
            $table->string('present_police_station', 100)->nullable()->after('present_post_office');
            $table->foreignId('present_district_id')->nullable()->constrained('districts')->nullOnDelete()->after('present_police_station');
            $table->foreignId('present_division_id')->nullable()->constrained('divisions')->nullOnDelete()->after('present_district_id');

            // Permanent Address
            $table->boolean('same_as_present')->default(false)->after('present_division_id');
            $table->string('permanent_house', 300)->nullable()->after('same_as_present');
            $table->string('permanent_post_office', 100)->nullable()->after('permanent_house');
            $table->string('permanent_police_station', 100)->nullable()->after('permanent_post_office');
            $table->foreignId('permanent_district_id')->nullable()->constrained('districts')->nullOnDelete()->after('permanent_police_station');
            $table->foreignId('permanent_division_id')->nullable()->constrained('divisions')->nullOnDelete()->after('permanent_district_id');

            // Meta
            $table->string('ip_address', 45)->nullable()->after('permanent_division_id');
        });
    }

    public function down(): void
    {
        Schema::table('admission_forms', function (Blueprint $table) {
            $table->dropForeign(['academic_session_id', 'blood_group_id', 'religion_id',
                'present_district_id', 'present_division_id',
                'permanent_district_id', 'permanent_division_id']);
            $table->dropColumn([
                'source', 'application_no', 'academic_session_id',
                'occupation', 'education_qualification',
                'ssc_school', 'ssc_board', 'ssc_year',
                'hsc_college', 'hsc_board', 'hsc_year',
                'university_name', 'department_name', 'device_type',
                'blood_group_id', 'passport_no', 'birth_certificate_no', 'nationality', 'religion_id',
                'present_house', 'present_post_office', 'present_police_station', 'present_district_id', 'present_division_id',
                'same_as_present',
                'permanent_house', 'permanent_post_office', 'permanent_police_station', 'permanent_district_id', 'permanent_division_id',
                'ip_address',
            ]);
        });
    }
};
