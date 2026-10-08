<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LearningResource;
use App\Models\SubjectModule;
use App\Models\Subject;

echo "Learning Resources count: " . LearningResource::count() . "\n";
foreach (LearningResource::all() as $lr) {
    echo "ID: {$lr->id}, Title: {$lr->title}, Type: {$lr->type}, URL: {$lr->url}, ModuleID: {$lr->module_id}\n";
}

echo "\nSubject Modules with files or drive_links:\n";
$modules = SubjectModule::with('subject')
    ->where(function($q) {
        $q->whereNotNull('file_path')->orWhereNotNull('drive_link');
    })->get();
echo "Modules with file or drive: " . $modules->count() . "\n";
foreach ($modules as $m) {
    echo "Module ID: {$m->id}, Subject: " . ($m->subject ? $m->subject->name : 'N/A') . 
         ", Title: {$m->title}, File: {$m->file_path}, Drive: {$m->drive_link}\n";
}
