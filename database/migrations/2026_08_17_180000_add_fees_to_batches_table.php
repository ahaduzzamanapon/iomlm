<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->decimal('admission_fee', 10, 2)->default(0.00)->after('is_admission_open');
            $table->decimal('monthly_fee', 10, 2)->default(0.00)->after('admission_fee');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn(['admission_fee', 'monthly_fee']);
        });
    }
};
