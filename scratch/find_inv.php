<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Course;
use App\Models\Invoice;

// Let's find any student who has a course with duration = 5 or where 1020 / 5 = 204
$allInvoices = Invoice::where('paid_amount', '>=', 1000)->with(['student.user', 'enrollment.course'])->get();
foreach ($allInvoices as $iv) {
    echo "Inv {$iv->id} [{$iv->category}]: Student {$iv->student_id} ({$iv->student?->user?->name}), Course: {$iv->enrollment?->course?->name} ({$iv->enrollment?->course?->type}), Payable: {$iv->payable_amount}, Paid: {$iv->paid_amount}, Due: {$iv->due_amount}\n";
}

// Check courses duration
foreach (Course::all() as $c) {
    if ($c->duration_value == 5 || $c->duration_value == 1020 || str_contains($c->name, 'Subject')) {
        echo "Course {$c->id}: {$c->name}, Type: {$c->type}, Dur: {$c->duration_value} {$c->duration_unit}\n";
    }
}
