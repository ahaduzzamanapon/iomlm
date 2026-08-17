<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════
        // FEE STRUCTURES (Master Rates)
        // ══════════════════════════════════════════════
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150); // e.g. Admission Fee, Semester 1 Fee, Retake Fee
            $table->enum('category', ['ADMISSION', 'SEMESTER', 'RETAKE', 'EXAM', 'DOCUMENT', 'OTHER'])->default('OTHER');
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ══════════════════════════════════════════════
        // INVOICES (Student Dues)
        // ══════════════════════════════════════════════
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 40)->unique();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('category', ['ADMISSION', 'SEMESTER', 'RETAKE', 'EXAM', 'DOCUMENT', 'FINE', 'MANUAL'])->default('MANUAL');
            $table->string('title', 200);
            $table->decimal('amount', 10, 2);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('payable_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('due_amount', 10, 2);
            $table->enum('status', ['UNPAID', 'PARTIAL', 'PAID', 'CANCELLED'])->default('UNPAID');
            $table->date('due_date')->nullable();
            
            // Polymorphic / source reference (e.g. AdmissionForm ID, SubjectRetake ID, etc.)
            $table->string('source_type', 100)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // PAYMENTS (Collection Transactions)
        // ══════════════════════════════════════════════
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no', 40)->unique();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['CASH', 'BKASH', 'NAGAD', 'ROCKET', 'BANK_TRANSFER', 'CARD', 'ONLINE'])->default('CASH');
            $table->string('transaction_id', 100)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('fee_structures');
    }
};
