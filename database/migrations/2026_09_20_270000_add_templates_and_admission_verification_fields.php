<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->text('sms_template')->nullable()->after('status');
            $table->text('email_template')->nullable()->after('sms_template');
        });

        Schema::table('admission_forms', function (Blueprint $table) {
            $table->decimal('approved_admission_fee', 10, 2)->nullable()->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('admission_forms', function (Blueprint $table) {
            $table->dropColumn('approved_admission_fee');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn(['sms_template', 'email_template']);
        });
    }
};
