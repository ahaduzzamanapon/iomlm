<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waiver_applications', function (Blueprint $table) {
            $table->boolean('is_used')->default(false)->after('status');
            $table->foreignId('admission_form_id')->nullable()->after('is_used')->constrained('admission_forms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('waiver_applications', function (Blueprint $table) {
            $table->dropForeign(['admission_form_id']);
            $table->dropColumn(['is_used', 'admission_form_id']);
        });
    }
};
