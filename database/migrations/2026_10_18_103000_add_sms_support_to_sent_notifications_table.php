<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('sent_notifications')) {
            // 1. Change channel from ENUM to VARCHAR(50) to support SMS and multi-channel combinations
            DB::statement("ALTER TABLE `sent_notifications` MODIFY `channel` VARCHAR(50) NOT NULL DEFAULT 'ALL'");

            // 2. Add sms_message and delivery count columns if not present
            Schema::table('sent_notifications', function (Blueprint $table) {
                if (!Schema::hasColumn('sent_notifications', 'sms_message')) {
                    $table->text('sms_message')->nullable()->after('message');
                }
                if (!Schema::hasColumn('sent_notifications', 'sms_count')) {
                    $table->unsignedInteger('sms_count')->default(0)->after('sent_count');
                }
                if (!Schema::hasColumn('sent_notifications', 'email_count')) {
                    $table->unsignedInteger('email_count')->default(0)->after('sms_count');
                }
                if (!Schema::hasColumn('sent_notifications', 'push_count')) {
                    $table->unsignedInteger('push_count')->default(0)->after('email_count');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sent_notifications')) {
            Schema::table('sent_notifications', function (Blueprint $table) {
                if (Schema::hasColumn('sent_notifications', 'push_count')) {
                    $table->dropColumn('push_count');
                }
                if (Schema::hasColumn('sent_notifications', 'email_count')) {
                    $table->dropColumn('email_count');
                }
                if (Schema::hasColumn('sent_notifications', 'sms_count')) {
                    $table->dropColumn('sms_count');
                }
                if (Schema::hasColumn('sent_notifications', 'sms_message')) {
                    $table->dropColumn('sms_message');
                }
            });

            DB::statement("ALTER TABLE `sent_notifications` MODIFY `channel` ENUM('PUSH', 'EMAIL', 'BOTH') NOT NULL DEFAULT 'BOTH'");
        }
    }
};
