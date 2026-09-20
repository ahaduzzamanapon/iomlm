<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'admin_permissions')) {
                $table->json('admin_permissions')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'designation')) {
                $table->string('designation', 120)->nullable()->after('admin_permissions');
            }
            if (!Schema::hasColumn('users', 'can_provide_support')) {
                $table->boolean('can_provide_support')->default(false)->after('designation');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('can_provide_support');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['admin_permissions', 'designation', 'can_provide_support', 'is_active']);
        });
    }
};
