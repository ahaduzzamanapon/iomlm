<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Public\AdmissionFormController;
use Illuminate\Http\Request;

try {
    $controller = new AdmissionFormController();
    $request = Request::create('/apply', 'GET');
    $response = $controller->show($request);
    
    if ($response instanceof \Illuminate\View\View) {
        $html = $response->render();
        echo "SUCCESS: apply view rendered cleanly! Output length: " . strlen($html) . " bytes\n";
        exit(0);
    } else {
        echo "Response was redirect or other type: " . get_class($response) . "\n";
        exit(0);
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
