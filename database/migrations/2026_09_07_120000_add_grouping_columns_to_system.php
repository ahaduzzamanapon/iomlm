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
        // 1. Semesters: dynamic grouping config
        Schema::table('semesters', function (Blueprint $table) {
            if (!Schema::hasColumn('semesters', 'has_groups')) {
                $table->boolean('has_groups')->default(false)->after('name');
            }
            if (!Schema::hasColumn('semesters', 'group_type')) {
                $table->string('group_type', 20)->default('NONE')->after('has_groups'); // NONE, GENDER, SPLIT
            }
            if (!Schema::hasColumn('semesters', 'split_count')) {
                $table->unsignedTinyInteger('split_count')->default(2)->after('group_type');
            }
        });

        // 2. Course Subject Maps: subject-level grouping override
        Schema::table('course_subject_maps', function (Blueprint $table) {
            if (!Schema::hasColumn('course_subject_maps', 'group_mode')) {
                $table->string('group_mode', 20)->default('INHERIT')->after('sort_order'); // INHERIT, NONE, GENDER, SPLIT
            }
        });

        // 3. Routine Entries: group tag for slot
        Schema::table('routine_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('routine_entries', 'group_tag')) {
                $table->string('group_tag', 30)->nullable()->default('ALL')->after('teacher_id'); // ALL, MALE, FEMALE, GROUP_A, GROUP_B
            }
        });

        // 4. Class Sessions: group tag for class occurrence
        Schema::table('class_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('class_sessions', 'group_tag')) {
                $table->string('group_tag', 30)->nullable()->default('ALL')->after('teacher_id');
            }
        });

        // 5. Enrollments: assigned split group tag (for split criteria)
        Schema::table('enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollments', 'group_tag')) {
                $table->string('group_tag', 30)->nullable()->after('admission_form_id'); // GROUP_A, GROUP_B, etc.
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropColumn(['has_groups', 'group_type', 'split_count']);
        });

        Schema::table('course_subject_maps', function (Blueprint $table) {
            $table->dropColumn('group_mode');
        });

        Schema::table('routine_entries', function (Blueprint $table) {
            $table->dropColumn('group_tag');
        });

        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropColumn('group_tag');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('group_tag');
        });
    }
};
