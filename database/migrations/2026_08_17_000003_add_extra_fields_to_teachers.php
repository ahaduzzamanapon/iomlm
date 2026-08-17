<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            // Personal Information
            $table->date('date_of_birth')->nullable()->after('joining_date');
            $table->string('gender', 20)->nullable()->after('date_of_birth');
            $table->string('religion', 50)->nullable()->after('gender');
            $table->string('nationality', 50)->nullable()->default('Bangladeshi')->after('religion');
            $table->string('national_id', 50)->nullable()->after('nationality');
            $table->string('passport_no', 50)->nullable()->after('national_id');
            $table->string('marital_status', 20)->nullable()->after('passport_no'); // Single, Married, Divorced, Widowed

            // Job Information
            $table->string('employment_type', 30)->nullable()->after('marital_status'); // Full-time, Part-time, Contract, Volunteer
            $table->decimal('salary', 10, 2)->nullable()->after('employment_type');
            $table->string('department', 100)->nullable()->after('salary');
            $table->text('bio')->nullable()->after('department');

            // Emergency Contact
            $table->string('emergency_contact_name', 200)->nullable()->after('bio');
            $table->string('emergency_contact_phone', 30)->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relation', 50)->nullable()->after('emergency_contact_phone');

            // Present Address
            $table->string('present_house', 300)->nullable()->after('emergency_contact_relation');
            $table->string('present_post_office', 100)->nullable()->after('present_house');
            $table->string('present_police_station', 100)->nullable()->after('present_post_office');
            $table->foreignId('present_district_id')->nullable()->constrained('districts')->nullOnDelete()->after('present_police_station');
            $table->foreignId('present_division_id')->nullable()->constrained('divisions')->nullOnDelete()->after('present_district_id');

            // Permanent Address
            $table->boolean('address_same_as_present')->default(false)->after('present_division_id');
            $table->string('permanent_house', 300)->nullable()->after('address_same_as_present');
            $table->string('permanent_post_office', 100)->nullable()->after('permanent_house');
            $table->string('permanent_police_station', 100)->nullable()->after('permanent_post_office');
            $table->foreignId('permanent_district_id')->nullable()->constrained('districts')->nullOnDelete()->after('permanent_police_station');
            $table->foreignId('permanent_division_id')->nullable()->constrained('divisions')->nullOnDelete()->after('permanent_district_id');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['present_district_id', 'present_division_id', 'permanent_district_id', 'permanent_division_id']);
            $table->dropColumn([
                'date_of_birth', 'gender', 'religion', 'nationality', 'national_id', 'passport_no', 'marital_status',
                'employment_type', 'salary', 'department', 'bio',
                'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
                'present_house', 'present_post_office', 'present_police_station', 'present_district_id', 'present_division_id',
                'address_same_as_present',
                'permanent_house', 'permanent_post_office', 'permanent_police_station', 'permanent_district_id', 'permanent_division_id',
            ]);
        });
    }
};
