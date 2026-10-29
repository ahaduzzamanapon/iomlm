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
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'facebook_live_approved')) {
                $table->boolean('facebook_live_approved')->default(false)->after('status')->index();
            }
            if (!Schema::hasColumn('students', 'facebook_live_approved_at')) {
                $table->timestamp('facebook_live_approved_at')->nullable()->after('facebook_live_approved');
            }
            if (!Schema::hasColumn('students', 'facebook_live_approved_by')) {
                $table->unsignedBigInteger('facebook_live_approved_by')->nullable()->after('facebook_live_approved_at');
            }
            if (!Schema::hasColumn('students', 'facebook_live_notes')) {
                $table->string('facebook_live_notes', 500)->nullable()->after('facebook_live_approved_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_live_approved',
                'facebook_live_approved_at',
                'facebook_live_approved_by',
                'facebook_live_notes',
            ]);
        });
    }
};
