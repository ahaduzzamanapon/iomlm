<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('payments', 'sender_number')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('sender_number', 30)->nullable()->after('transaction_id');
            });
        }

        if (!Schema::hasColumn('invoices', 'notes')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payments', 'sender_number')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('sender_number');
            });
        }

        if (!Schema::hasColumn('invoices', 'notes')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }
};
