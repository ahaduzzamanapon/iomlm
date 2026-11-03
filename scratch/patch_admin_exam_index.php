<?php

$filePath = __DIR__ . '/../resources/views/admin/exams/index.blade.php';
$content = file_get_contents($filePath);

// 1. Enctype in store form
$content = str_replace(
    '<form method="POST" action="{{ route(\'admin.exams.store\') }}">',
    '<form method="POST" action="{{ route(\'admin.exams.store\') }}" enctype="multipart/form-data">',
    $content
);

// 2. Enctype in edit form
$content = str_replace(
    '<form method="POST" id="editExamForm" action="">',
    '<form method="POST" id="editExamForm" action="" enctype="multipart/form-data">',
    $content
);

// 3. Add PDF upload field in Add Exam modal (after line 479)
$addPdfField = '                    <div class="form-group" style="margin-top:14px;background:#fdf2f8;padding:12px 14px;border:1px dashed #f472b6;border-radius:8px;font-family:\'Kalpurush\',sans-serif">
                        <label style="font-weight:700;font-size:12px;color:#9d174d;display:flex;align-items:center;gap:6px;margin-bottom:4px">
                            <i class="fa-solid fa-file-pdf" style="color:#ef4444;font-size:16px"></i> লিখিত পরীক্ষার মূল প্রশ্নপত্র (PDF আপলোড) <span style="font-size:11px;color:#64748b;font-weight:normal">(ঐচ্ছিক)</span>
                        </label>
                        <input type="file" name="written_question_pdf" accept=".pdf" class="form-control" style="font-size:12px">
                        <small style="color:#64748b;font-size:11px;margin-top:3px;display:block">শিক্ষার্থীরা লিখিত পরীক্ষায় এই পিডিএফ প্রশ্নপত্র দেখে বা ডাউনলোড করে খাতায় উত্তর লিখতে পারবে।</small>
                    </div>';

$targetAddLocation = '                        </div>' . "\n" . '                    </div>' . "\n\n" . '                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">';
$replacementAddLocation = '                        </div>' . "\n" . '                    </div>' . "\n\n" . $addPdfField . "\n\n" . '                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">';

$content = str_replace(
    str_replace("\r\n", "\n", $targetAddLocation),
    str_replace("\r\n", "\n", $replacementAddLocation),
    str_replace("\r\n", "\n", $content)
);

// 4. Add PDF upload field in Edit Exam modal (after edit table)
$editPdfField = '                    <div class="form-group" style="margin-top:14px;background:#fdf2f8;padding:12px 14px;border:1px dashed #f472b6;border-radius:8px;font-family:\'Kalpurush\',sans-serif">
                        <label style="font-weight:700;font-size:12px;color:#9d174d;display:flex;align-items:center;gap:6px;margin-bottom:4px">
                            <i class="fa-solid fa-file-pdf" style="color:#ef4444;font-size:16px"></i> লিখিত পরীক্ষার মূল প্রশ্নপত্র (PDF আপলোড) <span style="font-size:11px;color:#64748b;font-weight:normal">(ঐচ্ছিক)</span>
                        </label>
                        <div id="edit_written_pdf_preview" style="display:none;margin-bottom:8px;font-size:12px;align-items:center;gap:10px">
                            <a id="edit_written_pdf_link" href="#" target="_blank" class="btn btn-outline btn-sm" style="font-size:11px;color:#be185d;border-color:#f472b6;background:#fff">
                                <i class="fa-solid fa-eye"></i> বর্তমান প্রশ্নপত্র PDF দেখুন
                            </a>
                            <label style="font-size:11px;color:#dc2626;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                                <input type="checkbox" name="remove_written_pdf" value="1"> বিদ্যমান PDF মুছে ফেলুন
                            </label>
                        </div>
                        <input type="file" name="written_question_pdf" accept=".pdf" class="form-control" style="font-size:12px">
                    </div>';

$targetEditLocation = '                                    </tr>' . "\n" . '                                </tbody>' . "\n" . '                            </table>' . "\n" . '                        </div>' . "\n" . '                    </div>' . "\n\n" . '                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px">';
$replacementEditLocation = '                                    </tr>' . "\n" . '                                </tbody>' . "\n" . '                            </table>' . "\n" . '                        </div>' . "\n" . '                    </div>' . "\n\n" . $editPdfField . "\n\n" . '                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px">';

$content = str_replace(
    str_replace("\r\n", "\n", $targetEditLocation),
    str_replace("\r\n", "\n", $replacementEditLocation),
    str_replace("\r\n", "\n", $content)
);

// 5. Update JS in openEditModal
$targetJs = "calcAssessmentMarks('edit');";
$replacementJs = "const pdfPreview = document.getElementById('edit_written_pdf_preview');
        const pdfLink = document.getElementById('edit_written_pdf_link');
        if (exam.written_question_pdf) {
            pdfPreview.style.display = 'flex';
            pdfLink.href = '{{ asset('storage') }}/' + exam.written_question_pdf;
        } else {
            pdfPreview.style.display = 'none';
            pdfLink.href = '#';
        }
        calcAssessmentMarks('edit');";

$content = str_replace($targetJs, $replacementJs, $content);

file_put_contents($filePath, $content);
echo "Admin exams index modal updated.\n";
