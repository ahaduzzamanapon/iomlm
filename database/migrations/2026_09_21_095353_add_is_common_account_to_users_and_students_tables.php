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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_common_account')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_common_account')->default(false)->after('role');
            });
        }

        if (Schema::hasTable('students') && !Schema::hasColumn('students', 'is_common_account')) {
            Schema::table('students', function (Blueprint $table) {
                $table->boolean('is_common_account')->default(false)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_common_account')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_common_account');
            });
        }

        if (Schema::hasTable('students') && Schema::hasColumn('students', 'is_common_account')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('is_common_account');
            });
        }
    }
};
