<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Fetch all non-deleted admission forms
        $forms = DB::table('admission_forms')
            ->whereNull('deleted_at')
            ->get();

        foreach ($forms as $form) {
            $batch = null;

            // 1. If form has a batch_id, look up that batch
            if (!empty($form->batch_id)) {
                $batch = DB::table('batches')
                    ->where('id', $form->batch_id)
                    ->whereNull('deleted_at')
                    ->first();
            }

            // 2. If form has no batch_id, find the active/open batch for its course
            if (!$batch && !empty($form->interested_course_id)) {
                $batch = DB::table('batches')
                    ->where('course_id', $form->interested_course_id)
                    ->where('status', 'ACTIVE')
                    ->where('is_admission_open', true)
                    ->whereNull('deleted_at')
                    ->orderBy('id', 'asc')
                    ->first();

                // If no open batch, fallback to any active batch for that course
                if (!$batch) {
                    $batch = DB::table('batches')
                        ->where('course_id', $form->interested_course_id)
                        ->where('status', 'ACTIVE')
                        ->whereNull('deleted_at')
                        ->latest('id')
                        ->first();
                }

                // If an active batch is resolved, attach it to the form
                if ($batch) {
                    DB::table('admission_forms')
                        ->where('id', $form->id)
                        ->update(['batch_id' => $batch->id]);
                }
            }

            // 3. Resolve the correct academic session
            $correctSessionId = null;

            if ($batch && !empty($batch->academic_year_id)) {
                // Find active session for this batch's academic year
                $session = DB::table('academic_sessions')
                    ->where('academic_year_id', $batch->academic_year_id)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->latest('id')
                    ->first();

                if (!$session) {
                    $session = DB::table('academic_sessions')
                        ->where('academic_year_id', $batch->academic_year_id)
                        ->whereNull('deleted_at')
                        ->latest('id')
                        ->first();
                }

                if ($session) {
                    $correctSessionId = $session->id;
                }
            }

            // 4. Fallback: Find active session belonging to an active academic year
            if (!$correctSessionId) {
                $session = DB::table('academic_sessions')
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('academic_years')
                            ->whereColumn('academic_years.id', 'academic_sessions.academic_year_id')
                            ->where('is_active', true)
                            ->whereNull('deleted_at');
                    })
                    ->latest('id')
                    ->first();

                if (!$session) {
                    $session = DB::table('academic_sessions')
                        ->where('is_active', true)
                        ->whereNull('deleted_at')
                        ->latest('id')
                        ->first();
                }

                if ($session) {
                    $correctSessionId = $session->id;
                }
            }

            // 5. Update admission form if academic_session_id does not match
            if ($correctSessionId && (int)$form->academic_session_id !== (int)$correctSessionId) {
                DB::table('admission_forms')
                    ->where('id', $form->id)
                    ->update([
                        'academic_session_id' => $correctSessionId,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for data fix migration
    }
};
