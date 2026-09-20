<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\WaiverApplication;
use App\Models\AdmissionForm;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update application_no in waiver_applications: POOR-YYYY-XXXX -> PF-YYYY-XXXX
        DB::table('waiver_applications')
            ->where('application_no', 'like', 'POOR-%')
            ->get(['id', 'application_no'])
            ->each(function ($item) {
                $newNo = str_replace('POOR-', 'PF-', $item->application_no);
                DB::table('waiver_applications')
                    ->where('id', $item->id)
                    ->update(['application_no' => $newNo]);
            });

        // 2. Update waiver_code in admission_forms: POOR-YYYY-XXXX -> PF-YYYY-XXXX
        DB::table('admission_forms')
            ->where('waiver_code', 'like', 'POOR-%')
            ->get(['id', 'waiver_code'])
            ->each(function ($item) {
                $newCode = str_replace('POOR-', 'PF-', $item->waiver_code);
                DB::table('admission_forms')
                    ->where('id', $item->id)
                    ->update(['waiver_code' => $newCode]);
            });
    }

    public function down(): void
    {
        DB::table('waiver_applications')
            ->where('application_no', 'like', 'PF-%')
            ->get(['id', 'application_no'])
            ->each(function ($item) {
                $oldNo = str_replace('PF-', 'POOR-', $item->application_no);
                DB::table('waiver_applications')
                    ->where('id', $item->id)
                    ->update(['application_no' => $oldNo]);
            });

        DB::table('admission_forms')
            ->where('waiver_code', 'like', 'PF-%')
            ->get(['id', 'waiver_code'])
            ->each(function ($item) {
                $oldCode = str_replace('PF-', 'POOR-', $item->waiver_code);
                DB::table('admission_forms')
                    ->where('id', $item->id)
                    ->update(['waiver_code' => $oldCode]);
            });
    }
};
