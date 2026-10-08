<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Subject;
use App\Models\SubjectModule;

$subjects = Subject::where('name', 'like', '%Golpe%')
    ->orWhere('name', 'like', '%Tafseer%')
    ->orWhere('code', 'like', '%GGT%')
    ->orWhere('code', 'like', '%2608%')
    ->get();

echo "Found subjects: " . $subjects->count() . "\n";
foreach ($subjects as $s) {
    echo "ID: {$s->id}, Code: {$s->code}, Name: {$s->name}\n";
    foreach ($s->modules as $m) {
        echo "  Module: {$m->sequence_no} - {$m->title}, File: {$m->file_path}, Drive: {$m->drive_link}, Folder: {$m->folder_name}\n";
    }
}

if ($subjects->isEmpty()) {
    echo "\nAll Subjects in DB:\n";
    foreach (Subject::all() as $s) {
        echo "ID: {$s->id}, Code: {$s->code}, Name: {$s->name}, Modules: " . $s->modules()->count() . "\n";
    }
}
