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
        if (Schema::hasTable('payments')) {
            // Modify payment_method column from ENUM to VARCHAR(50) so methods like 'Bank', 'bKash', etc. are accepted
            DB::statement("ALTER TABLE `payments` MODIFY `payment_method` VARCHAR(50) NOT NULL DEFAULT 'CASH'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payments')) {
            DB::statement("ALTER TABLE `payments` MODIFY `payment_method` ENUM('CASH', 'BKASH', 'NAGAD', 'ROCKET', 'BANK_TRANSFER', 'CARD', 'ONLINE') NOT NULL DEFAULT 'CASH'");
        }
    }
};
