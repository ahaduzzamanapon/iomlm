<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Invoice;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix old semester invoices that were created with full multi-year package totals instead of per-semester rates
        $invoices = Invoice::where('category', 'SEMESTER')->where('amount', '>', 15000)->get();

        foreach ($invoices as $inv) {
            $enrollment = $inv->enrollment;
            $course     = $enrollment?->course;
            $totalSems  = max(1, $course?->semesters()->count() ?: 6);

            $oldAmount = (float) $inv->amount;
            $newAmount = round($oldAmount / $totalSems, 2);

            $inv->update([
                'amount'         => $newAmount,
                'payable_amount' => $newAmount,
                'due_amount'     => max(0, $newAmount - (float)$inv->paid_amount),
                'status'         => ($newAmount - (float)$inv->paid_amount) <= 0 ? 'PAID' : ((float)$inv->paid_amount > 0 ? 'PARTIAL' : 'UNPAID'),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed
    }
};
