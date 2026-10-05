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
        // 1. Add unique index to courses.code
        Schema::table('courses', function (Blueprint $table) {
            $table->string('code', 30)->change();
            $table->unique('code');
        });

        // 2. Add manual payment columns to admission_forms
        Schema::table('admission_forms', function (Blueprint $table) {
            if (!Schema::hasColumn('admission_forms', 'manual_payment_method')) {
                $table->string('manual_payment_method', 50)->nullable()->after('status');
                $table->string('manual_trx_id', 100)->nullable()->after('manual_payment_method');
                $table->string('manual_sender_phone', 30)->nullable()->after('manual_trx_id');
                $table->text('manual_payment_notes')->nullable()->after('manual_sender_phone');
                $table->dateTime('manual_payment_date')->nullable()->after('manual_payment_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique(['code']);
        });

        Schema::table('admission_forms', function (Blueprint $table) {
            $table->dropColumn([
                'manual_payment_method',
                'manual_trx_id',
                'manual_sender_phone',
                'manual_payment_notes',
                'manual_payment_date',
            ]);
        });
    }
};
