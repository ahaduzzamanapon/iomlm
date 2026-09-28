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
        Schema::table('class_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('class_sessions', 'recording_url')) {
                $table->string('recording_url', 500)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('class_sessions', 'recording_file')) {
                $table->string('recording_file', 500)->nullable()->after('recording_url');
            }
            if (!Schema::hasColumn('class_sessions', 'recording_embed')) {
                $table->text('recording_embed')->nullable()->after('recording_file');
            }
            if (!Schema::hasColumn('class_sessions', 'recorded_videos')) {
                $table->longText('recorded_videos')->nullable()->after('recording_embed');
            }
            if (!Schema::hasColumn('class_sessions', 'has_recording')) {
                $table->boolean('has_recording')->default(false)->index()->after('recorded_videos');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $cols = ['recording_url', 'recording_file', 'recording_embed', 'recorded_videos', 'has_recording'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('class_sessions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
