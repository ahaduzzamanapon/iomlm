<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->string('status', 30)->default('PLANNED')->change();
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->enum('status', ['PLANNED', 'ACTIVE', 'COMPLETED', 'CANCELLED'])->default('PLANNED')->change();
        });
    }
};
