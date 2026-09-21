<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sent_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('sent_notifications', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('sent_by');
            }
            if (!Schema::hasColumn('sent_notifications', 'status')) {
                $table->string('status', 30)->default('SENT')->after('scheduled_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sent_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('sent_notifications', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('sent_notifications', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
        });
    }
};
