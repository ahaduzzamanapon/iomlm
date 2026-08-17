<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waiver_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_no')->unique();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->date('date_of_birth')->nullable();
            $table->string('father_name')->nullable();
            $table->string('national_id')->nullable();
            $table->string('gender')->nullable();
            $table->boolean('is_abroad')->default(false);
            $table->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->string('country_name')->nullable();

            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->boolean('same_as_present')->default(false);

            $table->string('occupation')->nullable();
            $table->string('institution_or_business')->nullable();
            $table->boolean('is_present_iom_student')->default(false);
            $table->string('student_roll')->nullable();

            $table->string('source_of_income')->nullable();
            $table->decimal('monthly_income', 10, 2)->nullable();
            $table->string('guardian_phone')->nullable();
            $table->boolean('is_married')->default(false);

            $table->text('family_siblings_details')->nullable();
            $table->text('financial_problem_description')->nullable();

            $table->string('apply_reason_type')->default('Both'); // Admission Fee / Monthly Fee / Both
            $table->decimal('convenient_admission_fee', 10, 2)->nullable();
            $table->decimal('convenient_monthly_fee', 10, 2)->nullable();

            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->decimal('approved_discount_percent', 5, 2)->default(0);
            $table->text('reviewer_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->string('ip_address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiver_applications');
    }
};
