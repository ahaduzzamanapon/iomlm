<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Change from smallint to decimal(5,1) to allow values like 1.5
            $table->decimal('duration_value', 5, 1)->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration_value')->default(1)->change();
        });
    }
};
