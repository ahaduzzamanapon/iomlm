<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\EmailTemplate::seedDefaultTemplates();
echo "Templates in DB: " . \App\Models\EmailTemplate::count() . "\n";
foreach (\App\Models\EmailTemplate::all() as $t) {
    echo "- [{$t->id}] {$t->name} ({$t->category_label}) - Subject: {$t->subject}\n";
}
