<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Student;

echo "--- Students with duplicate emails ---\n";
$dupEmails = Student::select('email', \DB::raw('count(*) as c'))
    ->whereNotNull('email')
    ->where('email', '!=', '')
    ->groupBy('email')
    ->having('c', '>', 1)
    ->get();

foreach ($dupEmails as $d) {
    echo "Email: {$d->email} (count: {$d->c})\n";
    $sts = Student::where('email', $d->email)->get();
    foreach ($sts as $s) {
        $u = $s->user;
        echo "  Student ID={$s->id}, Code={$s->student_code}, UserID={$s->user_id}, UserEmail=" . ($u ? $u->email : 'NONE') . "\n";
    }
}
