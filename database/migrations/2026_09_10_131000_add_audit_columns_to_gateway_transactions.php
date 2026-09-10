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
        Schema::table('gateway_transactions', function (Blueprint $table) {
            $table->string('customer_name', 200)->nullable()->after('currency');
            $table->string('customer_phone', 30)->nullable()->after('customer_name');
            $table->string('customer_email', 150)->nullable()->after('customer_phone');
            $table->string('card_type', 60)->nullable()->after('gateway_trx_id'); // e.g. BKASH-BKash, VISA-IBBL, MASTER-DBBL
            $table->string('card_brand', 50)->nullable()->after('card_type');   // e.g. VISA, MASTER, BKASH
            $table->string('card_issuer', 100)->nullable()->after('card_brand'); // e.g. BRAC BANK, ISLAMI BANK
            $table->string('bank_status', 50)->nullable()->after('card_issuer');  // e.g. VALID, VALIDATED, Completed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gateway_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'customer_phone',
                'customer_email',
                'card_type',
                'card_brand',
                'card_issuer',
                'bank_status',
            ]);
        });
    }
};
