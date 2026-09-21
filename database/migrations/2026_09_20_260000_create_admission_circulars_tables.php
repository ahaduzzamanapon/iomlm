<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_circulars', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Adm Fall 2026 (Jul-Dec)"
            $table->string('short_name')->nullable(); // e.g. "Adm Fall 2026 (Jul-Dec)"
            $table->string('semester_name')->nullable(); // e.g. "Fall 2026 (Jul-Dec)"
            $table->string('session_year')->nullable(); // e.g. "2025-2026"
            $table->string('student_id_prefix', 20)->default('26'); // e.g. "26"
            $table->string('ugc_id_prefix', 50)->nullable();
            $table->string('student_id_suffix', 50)->nullable();
            $table->string('program_type', 50)->default('Any');
            $table->string('circular_status', 50)->default('Current'); // Current, Expired, Upcoming
            $table->boolean('is_enabled')->default(true); // Master online admission toggle
            $table->boolean('is_program_batch_map_enabled')->default(true);
            $table->text('remark')->nullable();
            $table->dateTime('exam_date')->nullable();
            $table->dateTime('admission_start_date')->nullable();
            $table->dateTime('admission_end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('admission_circular_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_circular_id')->constrained('admission_circulars')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->string('campus', 100)->default('Main Campus');
            $table->boolean('is_online_admission_enabled')->default(false);
            $table->timestamps();

            $table->unique(['admission_circular_id', 'course_id', 'campus'], 'circ_course_campus_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_circular_batches');
        Schema::dropIfExists('admission_circulars');
    }
};
