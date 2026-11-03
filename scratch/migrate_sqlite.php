<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => __DIR__ . '/../database/database.sqlite']);

// Run new migrations
try {
    \Illuminate\Support\Facades\Schema::table('exams', function ($table) {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('exams', 'written_question_pdf')) {
            $table->string('written_question_pdf', 500)->nullable();
        }
    });
    echo "exams table updated.\n";
} catch (\Throwable $e) {
    echo "exams update: " . $e->getMessage() . "\n";
}

try {
    if (!\Illuminate\Support\Facades\Schema::hasTable('assignment_appeals')) {
        \Illuminate\Support\Facades\Schema::create('assignment_appeals', function ($table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->text('reason');
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();
        });
        echo "assignment_appeals table created.\n";
    } else {
        echo "assignment_appeals table already exists.\n";
    }
} catch (\Throwable $e) {
    echo "assignment_appeals error: " . $e->getMessage() . "\n";
}
