<?php

$examCtrlPath = __DIR__ . '/../app/Http/Controllers/Admin/ExamController.php';
$ec = file_get_contents($examCtrlPath);

// In store:
$targetStore = '$startDatetime = $startTime ? "{$examDate} {$startTime}:00" : "{$examDate} 00:00:00";
        $endDatetime   = $endTime ? "{$endDate} {$endTime}:00" : "{$endDate} 23:59:59";';

$replacementStore = '$startDatetime = $startTime ? "{$examDate} {$startTime}:00" : "{$examDate} 00:00:00";
        $endDatetime   = $endTime ? "{$endDate} {$endTime}:00" : "{$endDate} 23:59:59";

        $pdfPath = null;
        if ($request->hasFile(\'written_question_pdf\')) {
            $pdfPath = $request->file(\'written_question_pdf\')->store(\'exams/written_pdfs\', \'public\');
        }';

$ec = str_replace(
    str_replace("\r\n", "\n", $targetStore),
    str_replace("\r\n", "\n", $replacementStore),
    str_replace("\r\n", "\n", $ec)
);

$targetCreate = "'has_written'      => \$hasWritten,
            'written_marks'    => \$writtenMarks,";

$replacementCreate = "'has_written'      => \$hasWritten,
            'written_marks'    => \$writtenMarks,
            'written_question_pdf' => \$pdfPath,";

$ec = str_replace(
    str_replace("\r\n", "\n", $targetCreate),
    str_replace("\r\n", "\n", $replacementCreate),
    str_replace("\r\n", "\n", $ec)
);

// In update:
$targetUpdate = '$exam->update($updateData);';

$replacementUpdate = 'if ($request->hasFile(\'written_question_pdf\')) {
                if ($exam->written_question_pdf && \Illuminate\Support\Facades\Storage::disk(\'public\')->exists($exam->written_question_pdf)) {
                    \Illuminate\Support\Facades\Storage::disk(\'public\')->delete($exam->written_question_pdf);
                }
                $updateData[\'written_question_pdf\'] = $request->file(\'written_question_pdf\')->store(\'exams/written_pdfs\', \'public\');
            } elseif ($request->boolean(\'remove_written_pdf\')) {
                if ($exam->written_question_pdf && \Illuminate\Support\Facades\Storage::disk(\'public\')->exists($exam->written_question_pdf)) {
                    \Illuminate\Support\Facades\Storage::disk(\'public\')->delete($exam->written_question_pdf);
                }
                $updateData[\'written_question_pdf\'] = null;
            }

            $exam->update($updateData);';

$ec = str_replace(
    str_replace("\r\n", "\n", $targetUpdate),
    str_replace("\r\n", "\n", $replacementUpdate),
    str_replace("\r\n", "\n", $ec)
);

file_put_contents($examCtrlPath, $ec);
echo "Admin ExamController updated with written_question_pdf handling.\n";
