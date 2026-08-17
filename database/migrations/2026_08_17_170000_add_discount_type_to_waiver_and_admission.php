<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waiver_applications', function (Blueprint $table) {
            $table->string('discount_type')->default('PERCENTAGE')->after('status'); // PERCENTAGE, FIXED
            $table->decimal('approved_discount_value', 10, 2)->default(0)->after('discount_type');
        });

        Schema::table('admission_forms', function (Blueprint $table) {
            $table->string('discount_type')->default('PERCENTAGE')->after('discount_percent'); // PERCENTAGE, FIXED
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('waiver_applications', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'approved_discount_value']);
        });

        Schema::table('admission_forms', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_amount']);
        });
    }
};
