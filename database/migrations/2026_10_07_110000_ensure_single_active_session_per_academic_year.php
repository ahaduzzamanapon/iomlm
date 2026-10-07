<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For each academic year, ensure only at most one session is active
        $yearsWithMultipleActiveSessions = DB::table('academic_sessions')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->select('academic_year_id')
            ->groupBy('academic_year_id')
            ->havingRaw('COUNT(id) > 1')
            ->pluck('academic_year_id');

        foreach ($yearsWithMultipleActiveSessions as $yearId) {
            $latestActiveSessionId = DB::table('academic_sessions')
                ->where('academic_year_id', $yearId)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->max('id');

            if ($latestActiveSessionId) {
                DB::table('academic_sessions')
                    ->where('academic_year_id', $yearId)
                    ->where('id', '!=', $latestActiveSessionId)
                    ->update(['is_active' => false]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
