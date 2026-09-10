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
        Schema::create('gateway_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tran_id', 60)->unique()->index();
            $table->string('gateway', 30); // 'sslcommerz', 'bkash'
            $table->string('gateway_mode', 20)->default('sandbox'); // 'sandbox', 'live'
            $table->foreignId('admission_form_id')->nullable()->constrained('admission_forms')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('BDT');
            $table->string('status', 30)->default('INITIATED')->index(); // INITIATED, PENDING, SUCCESS, FAILED, CANCELLED, EXPIRED
            $table->string('gateway_trx_id', 100)->nullable(); // Bank Transaction ID or bKash TrxID
            $table->string('val_id', 100)->nullable(); // SSLCommerz val_id
            $table->string('payment_id', 100)->nullable(); // bKash paymentID
            $table->longText('raw_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->unsignedInteger('check_attempts')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gateway_transactions');
    }
};
