<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_appeals', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_appeals', 'fee_amount')) {
                $table->decimal('fee_amount', 10, 2)->default(0.00)->after('status');
            }
            if (!Schema::hasColumn('exam_appeals', 'payment_status')) {
                $table->string('payment_status', 20)->default('PAID')->after('fee_amount');
            }
            if (!Schema::hasColumn('exam_appeals', 'invoice_id')) {
                $table->foreignId('invoice_id')->nullable()->after('payment_status')->constrained('invoices')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_appeals', function (Blueprint $table) {
            if (Schema::hasColumn('exam_appeals', 'invoice_id')) {
                $table->dropForeign(['invoice_id']);
                $table->dropColumn('invoice_id');
            }
            if (Schema::hasColumn('exam_appeals', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('exam_appeals', 'fee_amount')) {
                $table->dropColumn('fee_amount');
            }
        });
    }
};
