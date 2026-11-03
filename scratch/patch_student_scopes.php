<?php

// Fix ClassController
$classControllerPath = __DIR__ . '/../app/Http/Controllers/Student/ClassController.php';
$content = file_get_contents($classControllerPath);

$target1 = '            if ($studentGender) {
                $q->orWhere(\'group_tag\', $studentGender);
            }
            if (!empty($enrollmentGroups)) {
                $q->orWhereIn(\'group_tag\', $enrollmentGroups);
            }';

$replacement1 = '            if (!empty($enrollmentGroups)) {
                $q->orWhereIn(\'group_tag\', $enrollmentGroups);
            } elseif ($studentGender) {
                $q->orWhere(\'group_tag\', $studentGender);
            }';

// Normalize CRLF to LF for matching, then restore or replace directly
$content = str_replace(
    str_replace("\r\n", "\n", $target1),
    str_replace("\r\n", "\n", $replacement1),
    str_replace("\r\n", "\n", $content)
);

// Also fix join method in ClassController
$targetJoin = '            $studentGender = strtoupper($student->gender ?? \'\');
            $allowed = false;
            if ($class->group_tag === \'MALE\' && $studentGender === \'MALE\') $allowed = true;
            elseif ($class->group_tag === \'FEMALE\' && $studentGender === \'FEMALE\') $allowed = true;
            elseif ($enrollment->group_tag === $class->group_tag) $allowed = true;';

$replacementJoin = '            $studentGender = strtoupper($student->gender ?? \'\');
            $effectiveGroup = $enrollment->group_tag ?: $studentGender;
            $allowed = ($effectiveGroup === $class->group_tag);';

$content = str_replace(
    str_replace("\r\n", "\n", $targetJoin),
    str_replace("\r\n", "\n", $replacementJoin),
    $content
);

file_put_contents($classControllerPath, $content);
echo "ClassController patched.\n";

// Fix RoutineController
$routineControllerPath = __DIR__ . '/../app/Http/Controllers/Student/RoutineController.php';
$rContent = file_get_contents($routineControllerPath);

$rTarget = '                if ($studentGender) {
                    $q->orWhere(\'group_tag\', $studentGender);
                }
                if (!empty($enrollmentGroups)) {
                    $q->orWhereIn(\'group_tag\', $enrollmentGroups);
                }';

$rReplacement = '                if (!empty($enrollmentGroups)) {
                    $q->orWhereIn(\'group_tag\', $enrollmentGroups);
                } elseif ($studentGender) {
                    $q->orWhere(\'group_tag\', $studentGender);
                }';

$rContent = str_replace(
    str_replace("\r\n", "\n", $rTarget),
    str_replace("\r\n", "\n", $rReplacement),
    str_replace("\r\n", "\n", $rContent)
);

file_put_contents($routineControllerPath, $rContent);
echo "RoutineController patched.\n";
