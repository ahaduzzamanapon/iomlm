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
        DB::statement("ALTER TABLE support_tickets MODIFY COLUMN status ENUM('PENDING', 'IN_PROGRESS', 'SOLVED', 'CLOSED') NOT NULL DEFAULT 'PENDING'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE support_tickets MODIFY COLUMN status ENUM('PENDING', 'IN_PROGRESS', 'CLOSED') NOT NULL DEFAULT 'PENDING'");
    }
};
