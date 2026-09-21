<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use App\Models\Course;

echo "Columns: " . json_encode(Schema::getColumnListing('courses')) . "\n";
echo "Total courses: " . Course::count() . "\n";
foreach (Course::all() as $c) {
    echo "ID: {$c->id}, Name: {$c->name}, Dept ID: " . ($c->department_id ?? 'NULL') . "\n";
}
if (Schema::hasTable('departments')) {
    echo "\nDepartments table exists! Columns: " . json_encode(Schema::getColumnListing('departments')) . "\n";
    foreach (\Illuminate\Support\Facades\DB::table('departments')->get() as $d) {
        echo "Dept ID: {$d->id}, Name: " . ($d->name ?? $d->title ?? 'N/A') . "\n";
    }
} else {
    echo "\nNo departments table.\n";
}
