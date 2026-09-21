<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_forms', function (Blueprint $table) {
            $table->foreignId('admission_circular_id')
                ->nullable()
                ->after('interested_course_id')
                ->constrained('admission_circulars')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('admission_forms', function (Blueprint $table) {
            $table->dropForeign(['admission_circular_id']);
            $table->dropColumn('admission_circular_id');
        });
    }
};
