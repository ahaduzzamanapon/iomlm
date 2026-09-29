<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('start_month', 20)->nullable()->after('duration_unit');
            $table->string('end_month', 20)->nullable()->after('start_month');
            $table->string('fee_start_month', 20)->nullable()->after('end_month');
            $table->string('fee_end_month', 20)->nullable()->after('fee_start_month');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->string('start_month', 20)->nullable()->after('expected_end_date');
            $table->string('end_month', 20)->nullable()->after('start_month');
            $table->string('fee_start_month', 20)->nullable()->after('end_month');
            $table->string('fee_end_month', 20)->nullable()->after('fee_start_month');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['start_month', 'end_month', 'fee_start_month', 'fee_end_month']);
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn(['start_month', 'end_month', 'fee_start_month', 'fee_end_month']);
        });
    }
};
