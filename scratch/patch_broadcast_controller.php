<?php

$filePath = __DIR__ . '/../app/Http/Controllers/Admin/BroadcastNotificationController.php';
$content = file_get_contents($filePath);

// Replace lines 153-155
$target1 = '                $uRoll   = $user->student->roll_no ?? \'\';
                $uCourse = $user->student->batch->course->name ?? \'\';
                $uBatch  = $user->student->batch->name ?? \'\';';

$replacement1 = '                $uRoll   = $user->student?->roll_no ?? $user->student?->student_code ?? \'\';
                $uBatchObj = $user->student?->batch ?? $user->student?->enrollments?->first()?->batch;
                $uCourse = $uBatchObj?->course?->name ?? \'\';
                $uBatch  = $uBatchObj?->name ?? \'\';';

$content = str_replace(
    str_replace("\r\n", "\n", $target1),
    str_replace("\r\n", "\n", $replacement1),
    str_replace("\r\n", "\n", $content)
);

// Replace lines 184-186
$target2 = '                    $uRoll   = $user->student->roll_no ?? \'\';
                    $uCourse = $user->student->batch->course->name ?? \'\';
                    $uBatch  = $user->student->batch->name ?? \'\';';

$replacement2 = '                    $uRoll   = $user->student?->roll_no ?? $user->student?->student_code ?? \'\';
                    $uBatchObj = $user->student?->batch ?? $user->student?->enrollments?->first()?->batch;
                    $uCourse = $uBatchObj?->course?->name ?? \'\';
                    $uBatch  = $uBatchObj?->name ?? \'\';';

$content = str_replace(
    str_replace("\r\n", "\n", $target2),
    str_replace("\r\n", "\n", $replacement2),
    str_replace("\r\n", "\n", $content)
);

file_put_contents($filePath, $content);
echo "BroadcastNotificationController patched.\n";
