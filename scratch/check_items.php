<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pkg = \App\Models\CourseFeePackage::find(17);
$semesterItems = $pkg->items()
    ->whereDoesntHave('feeHead', function ($q) {
        $q->whereIn('slug', ['admission_fee', 'retake_fee']);
    })
    ->where(function ($q) {
        $q->whereNull('label')
          ->orWhere(function ($q2) {
              $q2->where('label', 'not like', '%admission%')
                 ->where('label', 'not like', '%ভর্তি%')
                 ->where('label', 'not like', '%retake%')
                 ->where('label', 'not like', '%annual%')
                 ->where('label', 'not like', '%বার্ষিক%');
          });
    });

echo "Included items:\n";
foreach ($semesterItems->get() as $it) {
    echo "  - {$it->label} ({$it->feeHead?->slug}): total = {$it->total_amount}, perUnit = {$it->amount_per_unit}\n";
}
echo "Sum total: " . $semesterItems->sum('total_amount') . "\n";
