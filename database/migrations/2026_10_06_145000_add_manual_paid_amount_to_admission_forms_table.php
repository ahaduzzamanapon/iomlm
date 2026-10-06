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
        Schema::table('admission_forms', function (Blueprint $table) {
            if (!Schema::hasColumn('admission_forms', 'manual_paid_amount')) {
                $table->decimal('manual_paid_amount', 10, 2)->nullable()->after('manual_payment_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission_forms', function (Blueprint $table) {
            if (Schema::hasColumn('admission_forms', 'manual_paid_amount')) {
                $table->dropColumn('manual_paid_amount');
            }
        });
    }
};
