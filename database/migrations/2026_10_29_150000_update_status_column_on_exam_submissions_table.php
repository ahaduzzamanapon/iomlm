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
        // Alter status column to VARCHAR(50) so all submission statuses including AUTO_SUBMITTED_TIMEOUT are supported
        DB::statement("ALTER TABLE `exam_submissions` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'SUBMITTED'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `exam_submissions` MODIFY COLUMN `status` ENUM('IN_PROGRESS', 'SUBMITTED', 'AUTO_SUBMITTED_VIOLATION') NOT NULL DEFAULT 'SUBMITTED'");
    }
};
