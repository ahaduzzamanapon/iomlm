<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AdmissionForm;

$form = AdmissionForm::find(67);
if ($form) {
    print_r($form->toArray());
}
