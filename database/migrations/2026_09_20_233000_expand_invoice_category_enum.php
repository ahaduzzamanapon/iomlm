<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE invoices MODIFY COLUMN category VARCHAR(50) NOT NULL DEFAULT 'MANUAL'");
            }
        } catch (\Throwable $e) {
            // fallback if needed
        }
    }

    public function down(): void
    {
        try {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE invoices MODIFY COLUMN category ENUM('ADMISSION', 'SEMESTER', 'RETAKE', 'READMISSION', 'COURSE_TRANSFER', 'EXAM', 'DOCUMENT', 'FINE', 'MANUAL', 'EXTRA') NOT NULL DEFAULT 'MANUAL'");
            }
        } catch (\Throwable $e) {}
    }
};
