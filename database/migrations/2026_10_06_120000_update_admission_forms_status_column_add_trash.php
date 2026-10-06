<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_forms', function (Blueprint $table) {
            $table->string('status', 50)->default('PENDING')->change();
        });

        // Convert existing REJECTED admissions to TRASH
        DB::table('admission_forms')->where('status', 'REJECTED')->update(['status' => 'TRASH']);
    }

    public function down(): void
    {
        DB::table('admission_forms')->where('status', 'TRASH')->update(['status' => 'REJECTED']);

        Schema::table('admission_forms', function (Blueprint $table) {
            $table->string('status', 50)->default('PENDING')->change();
        });
    }
};
