<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_forms', function (Blueprint $table) {
            $table->string('waiver_code')->nullable()->after('lead_source');
        });
    }

    public function down(): void
    {
        Schema::table('admission_forms', function (Blueprint $table) {
            $table->dropColumn('waiver_code');
        });
    }
};
