<?php

$filePath = __DIR__ . '/../resources/views/admin/questions/index.blade.php';
$content = file_get_contents($filePath);

// Replace in mcq_batch_select, written_batch_select, bulk_batch_select, aiken_batch_select, edit_batch_id
$pattern = '/<option value="\{\{\s*\$b->id\s*\}\}" data-course-id="\{\{\s*\$b->course_id\s*\}\}">\s*\{\{\s*\$b->name\s*\}\}\s*<\/option>/';
$replacement = '<option value="{{ $b->id }}" data-course-id="{{ $b->course_id }}">{{ $b->name }}{{ $b->course ? \' (\' . $b->course->name . \')\' : \'\' }}</option>';

$newContent = preg_replace($pattern, $replacement, $content);

file_put_contents($filePath, $newContent);
echo "Questions index batch selects patched.\n";
