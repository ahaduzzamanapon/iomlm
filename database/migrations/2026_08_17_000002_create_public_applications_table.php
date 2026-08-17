<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_no', 30)->unique(); // APP-2026-0001

            // Step 1: Course Choice
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();

            // Step 2: Education Info
            $table->string('applicant_name', 200);
            $table->string('phone', 30);
            $table->date('date_of_birth')->nullable();
            $table->string('occupation', 100)->nullable();          // Student, Service, Business, Other
            $table->string('education_qualification', 100)->nullable(); // SSC, HSC, BA, MA etc.
            $table->string('ssc_school', 200)->nullable();
            $table->string('ssc_board', 100)->nullable();
            $table->unsignedSmallInteger('ssc_year')->nullable();
            $table->string('hsc_college', 200)->nullable();
            $table->string('hsc_board', 100)->nullable();
            $table->unsignedSmallInteger('hsc_year')->nullable();
            $table->string('university_name', 200)->nullable();
            $table->string('department_name', 100)->nullable();
            $table->string('device_type', 50)->nullable();          // Desktop, Mobile, Tablet

            // Step 3: Personal Information
            $table->string('gender', 20)->nullable();
            $table->foreignId('blood_group_id')->nullable()->constrained('blood_groups')->nullOnDelete();
            $table->string('email', 150)->nullable();
            $table->string('national_id', 50)->nullable();
            $table->string('passport_no', 50)->nullable();
            $table->string('birth_certificate_no', 50)->nullable();
            $table->string('nationality', 50)->default('Bangladeshi');
            $table->foreignId('religion_id')->nullable()->constrained('religions')->nullOnDelete();

            // Present Address
            $table->string('present_house', 300)->nullable();
            $table->string('present_post_office', 100)->nullable();
            $table->string('present_police_station', 100)->nullable();
            $table->foreignId('present_district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('present_division_id')->nullable()->constrained('divisions')->nullOnDelete();

            // Permanent Address
            $table->boolean('same_as_present')->default(false);
            $table->string('permanent_house', 300)->nullable();
            $table->string('permanent_post_office', 100)->nullable();
            $table->string('permanent_police_station', 100)->nullable();
            $table->foreignId('permanent_district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('permanent_division_id')->nullable()->constrained('divisions')->nullOnDelete();

            // Meta
            $table->enum('status', ['PENDING', 'REVIEWED', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->text('admin_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_applications');
    }
};
