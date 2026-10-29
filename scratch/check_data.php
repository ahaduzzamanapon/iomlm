<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== EMAIL TEMPLATES ===\n";
foreach (\App\Models\EmailTemplate::all() as $t) {
    echo "ID: {$t->id} | Name: {$t->name} | Cat: {$t->category} | Subj: {$t->subject}\n";
    echo "Content snippet: " . substr($t->content, 0, 100) . "...\n\n";
}

echo "=== BATCHES SMS TEMPLATES ===\n";
foreach (\App\Models\Batch::whereNotNull('sms_template')->get() as $b) {
    echo "Batch {$b->id} ({$b->name}): {$b->sms_template}\n";
}

echo "=== SETTINGS WITH SMS/TEMPLATE ===\n";
foreach (\App\Models\Setting::where('key', 'like', '%sms%')->orWhere('key', 'like', '%template%')->get() as $s) {
    echo "{$s->key} = {$s->value}\n";
}
