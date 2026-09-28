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
        Schema::table('subject_modules', function (Blueprint $table) {
            if (!Schema::hasColumn('subject_modules', 'recorded_videos')) {
                $table->longText('recorded_videos')->nullable()->after('embed_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_modules', function (Blueprint $table) {
            if (Schema::hasColumn('subject_modules', 'recorded_videos')) {
                $table->dropColumn('recorded_videos');
            }
        });
    }
};
