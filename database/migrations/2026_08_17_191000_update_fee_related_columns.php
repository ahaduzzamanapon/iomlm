<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add admission_fee to courses
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('admission_fee', 10, 2)->default(0.00)->after('is_active');
        });

        // Update waiver_applications: rename apply_reason_type → apply_for with proper enum
        // and add approved_package_id + approved_admission_fee
        Schema::table('waiver_applications', function (Blueprint $table) {
            // apply_for: what type of waiver is requested
            $table->enum('apply_for', ['ADMISSION_FEE', 'TUITION_FEE', 'BOTH'])
                  ->default('BOTH')->after('apply_reason_type');

            // Admin sets approved admission fee (absolute amount student will pay)
            $table->decimal('approved_admission_fee', 10, 2)->nullable()->after('approved_discount_value');

            // Admin selects which fee package (for tuition fee waiver)
            $table->foreignId('approved_package_id')
                  ->nullable()
                  ->constrained('course_fee_packages')
                  ->nullOnDelete()
                  ->after('approved_admission_fee');

            // course_id the waiver is for (to load packages on approval)
            $table->foreignId('course_id')
                  ->nullable()
                  ->constrained('courses')
                  ->nullOnDelete()
                  ->after('apply_for');
        });

        // Add retake_fee to subject_retakes
        Schema::table('subject_retakes', function (Blueprint $table) {
            $table->decimal('retake_fee', 10, 2)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('subject_retakes', function (Blueprint $table) {
            $table->dropColumn('retake_fee');
        });

        Schema::table('waiver_applications', function (Blueprint $table) {
            $table->dropForeign(['approved_package_id']);
            $table->dropForeign(['course_id']);
            $table->dropColumn(['apply_for', 'approved_admission_fee', 'approved_package_id', 'course_id']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('admission_fee');
        });
    }
};
