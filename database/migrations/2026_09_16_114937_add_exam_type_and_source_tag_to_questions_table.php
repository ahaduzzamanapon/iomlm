<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('exam_type', 50)->nullable()->after('difficulty')->index();
            $table->string('source_tag', 150)->nullable()->after('exam_type')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['exam_type']);
            $table->dropIndex(['source_tag']);
            $table->dropColumn(['exam_type', 'source_tag']);
        });
    }
};
