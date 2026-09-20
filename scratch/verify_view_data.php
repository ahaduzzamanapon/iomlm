<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\FinalMarkController;
use Illuminate\Http\Request;
use App\Models\User;

$admin = User::whereIn('role', ['admin', 'super_admin'])->first() ?? User::first();
auth()->guard('web')->login($admin);

$controller = new FinalMarkController();

// Test batch 6 and subject 4
$req1 = Request::create('/admin/final-marks', 'GET', ['batch_id' => 6, 'subject_id' => 4]);
$view1 = $controller->index($req1);
$data1 = $view1->getData();
echo "Batch 6 & Subject 4 FinalMarks Count: " . $data1['finalMarks']->count() . "\n";

// Test batch 21 and subject 4
$req2 = Request::create('/admin/final-marks', 'GET', ['batch_id' => 21, 'subject_id' => 4]);
$view2 = $controller->index($req2);
$data2 = $view2->getData();
echo "Batch 21 & Subject 4 FinalMarks Count: " . $data2['finalMarks']->count() . "\n";

echo "Rendered successfully without error.\n";
