<x-teacher-layout>
    <x-slot name="title">নম্বর মূল্যায়ন ও এন্ট্রি — {{ $exam->title }}</x-slot>

    <div class="page-header" style="font-family:'Kalpurush', sans-serif">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('teacher.results.index') }}" style="color:#2563eb;text-decoration:none">← পরীক্ষার ফলাফল তালিকায় ফিরে যান</a>
            </div>
            <h1 style="font-size:22px;font-weight:800;color:#1e293b;margin:0 0 6px">নম্বর এন্ট্রি ও মূল্যায়ন শিট: {{ $exam->title }}</h1>
            <p style="color:#64748b;margin:0;font-size:13px">
                বিষয়: <strong>{{ $exam->subject->name ?? '—' }}</strong> · পূর্ণমান: <strong>{{ $exam->full_marks }}</strong> · পাস নম্বর: <strong>{{ $exam->pass_marks }}</strong>
            </p>
        </div>
    </div>

    {{-- Component Summary Banner --}}
    @php
        $activeComps = $exam->getActiveComponents();
        $hasComps = !empty($activeComps);
    @endphp

    <div class="card" style="margin-bottom:20px;padding:16px 20px;border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;font-family:'Kalpurush', sans-serif">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div style="font-size:14px;font-weight:700;color:#334155;display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-layer-group" style="color:#6366f1"></i> এই পরীক্ষার সক্রিয় মূল্যায়ন কাঠামো:
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
                @if($exam->has_mcq)
                    <span style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;padding:4px 10px;border-radius:8px;font-size:12px;font-weight:700">
                        <i class="fa-solid fa-list-check"></i> MCQ: {{ $exam->mcq_marks }} নম্বর
                    </span>
                @endif
                @if($exam->has_written)
                    <span style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:4px 10px;border-radius:8px;font-size:12px;font-weight:700">
                        <i class="fa-solid fa-pen-nib"></i> লিখিত: {{ $exam->written_marks }} নম্বর
                    </span>
                @endif
                @if($exam->has_tamrin)
                    <span style="background:#f3e8ff;color:#7e22ce;border:1px solid #e9d5ff;padding:4px 10px;border-radius:8px;font-size:12px;font-weight:700">
                        <i class="fa-solid fa-book-open"></i> তামরিন: {{ $exam->tamrin_marks }} নম্বর
                    </span>
                @endif
                @if($exam->has_viva)
                    <span style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;padding:4px 10px;border-radius:8px;font-size:12px;font-weight:700">
                        <i class="fa-solid fa-microphone-lines"></i> ভাইভা: {{ $exam->viva_marks }} নম্বর
                    </span>
                @endif
                <span style="background:#0f172a;color:#fff;padding:4px 12px;border-radius:8px;font-size:12px;font-weight:800">
                    মোট পূর্ণমান: {{ $exam->full_marks }}
                </span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('teacher.results.store', $exam) }}" id="marksEntryForm" style="font-family:'Kalpurush', sans-serif">
        @csrf
        <div class="card" style="border-radius:14px;overflow:hidden;border:1px solid #e2e8f0">
            <div class="card-header" style="background:#fff;padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between">
                <span class="card-title" style="font-size:15px;font-weight:800;color:#0f172a">
                    <i class="fa-solid fa-user-graduate" style="color:#2563eb"></i> শিক্ষার্থী মূল্যায়ন তালিকা (Student Evaluation Sheet)
                </span>
                <span style="font-size:12px;color:#64748b;font-weight:600">
                    মোট শিক্ষার্থী: <strong>{{ $students->count() }}</strong> জন
                </span>
            </div>
            <div class="table-wrapper">
                <table style="width:100%;border-collapse:collapse;font-size:13px">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
                            <th style="padding:12px 14px;text-align:left;width:120px">আইডি / রোল</th>
                            <th style="padding:12px 14px;text-align:left">শিক্ষার্থীর নাম</th>
                            
                            @if($exam->has_mcq)
                                <th style="padding:12px 10px;text-align:center;background:#eff6ff;color:#1e40af;width:120px">
                                    MCQ<br><small style="font-weight:600">/{{ $exam->mcq_marks }}</small>
                                </th>
                            @endif

                            @if($exam->has_written)
                                <th style="padding:12px 10px;text-align:center;background:#fef3c7;color:#92400e;width:120px">
                                    লিখিত<br><small style="font-weight:600">/{{ $exam->written_marks }}</small>
                                </th>
                            @endif

                            @if($exam->has_tamrin)
                                <th style="padding:12px 10px;text-align:center;background:#f3e8ff;color:#7e22ce;width:120px">
                                    তামরিন<br><small style="font-weight:600">/{{ $exam->tamrin_marks }}</small>
                                </th>
                            @endif

                            @if($exam->has_viva)
                                <th style="padding:12px 10px;text-align:center;background:#ecfdf5;color:#047857;width:120px">
                                    ভাইভা<br><small style="font-weight:600">/{{ $exam->viva_marks }}</small>
                                </th>
                            @endif

                            <th style="padding:12px 14px;text-align:center;background:#f1f5f9;font-weight:800;color:#0f172a;width:130px">
                                মোট নম্বর<br><small style="color:#64748b;font-weight:700">/{{ $exam->full_marks }}</small>
                            </th>
                            <th style="padding:12px 12px;text-align:center;width:90px">গ্রেড</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $st)
                        @php
                            $existingRes = $exam->results->where('student_id', $st->id)->first();
                            $submission  = $exam->submissions->where('student_id', $st->id)->first();

                            $mcqVal = $existingRes?->mcq_marks ?? $submission?->mcq_score ?? '';
                            $writtenVal = $existingRes?->written_marks ?? $submission?->written_score ?? '';
                            $tamrinVal = $existingRes?->tamrin_marks ?? $submission?->tamrin_score ?? '';
                            $vivaVal = $existingRes?->viva_marks ?? $submission?->viva_score ?? '';
                            $totalVal = $existingRes?->marks ?? $submission?->total_score ?? '';
                        @endphp
                        <tr class="eval-row" data-student-id="{{ $st->id }}" style="border-bottom:1px solid #f1f5f9;transition:background .15s">
                            <td style="padding:12px 14px">
                                <span style="font-family:monospace;font-weight:700;background:#f1f5f9;color:#334155;padding:3px 8px;border-radius:6px;font-size:12px">
                                    {{ $st->student_code ?? $st->student_id ?? '—' }}
                                </span>
                            </td>
                            <td style="padding:12px 14px">
                                <strong style="color:#0f172a;font-size:13.5px">{{ $st->name }}</strong>
                                @if($submission)
                                    <div style="font-size:11px;color:#10b981;font-weight:600;margin-top:2px">
                                        <i class="fa-solid fa-circle-check"></i> অনলাইনে জমা পড়েছে
                                    </div>
                                @endif
                            </td>

                            {{-- MCQ Field --}}
                            @if($exam->has_mcq)
                                <td style="padding:10px;text-align:center;background:#f8fafc">
                                    <input type="number" step="0.25" min="0" max="{{ $exam->mcq_marks }}"
                                           name="mcq_marks[{{ $st->id }}]"
                                           class="form-control comp-input comp-mcq"
                                           data-max="{{ $exam->mcq_marks }}"
                                           value="{{ $mcqVal !== '' ? $mcqVal : '' }}"
                                           placeholder="0"
                                           style="width:95px;margin:0 auto;text-align:center;font-weight:700;height:36px;border-radius:8px">
                                </td>
                            @endif

                            {{-- Written Field --}}
                            @if($exam->has_written)
                                <td style="padding:10px;text-align:center;background:#fffdf7">
                                    <input type="number" step="0.5" min="0" max="{{ $exam->written_marks }}"
                                           name="written_marks[{{ $st->id }}]"
                                           class="form-control comp-input comp-written"
                                           data-max="{{ $exam->written_marks }}"
                                           value="{{ $writtenVal !== '' ? $writtenVal : '' }}"
                                           placeholder="0"
                                           style="width:95px;margin:0 auto;text-align:center;font-weight:700;height:36px;border-radius:8px">
                                </td>
                            @endif

                            {{-- Tamrin Field --}}
                            @if($exam->has_tamrin)
                                <td style="padding:10px;text-align:center;background:#faf5ff">
                                    <input type="number" step="0.5" min="0" max="{{ $exam->tamrin_marks }}"
                                           name="tamrin_marks[{{ $st->id }}]"
                                           class="form-control comp-input comp-tamrin"
                                           data-max="{{ $exam->tamrin_marks }}"
                                           value="{{ $tamrinVal !== '' ? $tamrinVal : '' }}"
                                           placeholder="0"
                                           style="width:95px;margin:0 auto;text-align:center;font-weight:700;height:36px;border-radius:8px">
                                </td>
                            @endif

                            {{-- Viva Field --}}
                            @if($exam->has_viva)
                                <td style="padding:10px;text-align:center;background:#f0fdf4">
                                    <input type="number" step="0.5" min="0" max="{{ $exam->viva_marks }}"
                                           name="viva_marks[{{ $st->id }}]"
                                           class="form-control comp-input comp-viva"
                                           data-max="{{ $exam->viva_marks }}"
                                           value="{{ $vivaVal !== '' ? $vivaVal : '' }}"
                                           placeholder="0"
                                           style="width:95px;margin:0 auto;text-align:center;font-weight:700;height:36px;border-radius:8px">
                                </td>
                            @endif

                            {{-- Total Marks Field --}}
                            <td style="padding:10px;text-align:center;background:#f8fafc">
                                @if($hasComps)
                                    <input type="number" step="0.25" min="0" max="{{ $exam->full_marks }}"
                                           name="marks[{{ $st->id }}]"
                                           class="form-control row-total"
                                           value="{{ $totalVal !== '' ? $totalVal : '' }}"
                                           readonly
                                           style="width:100px;margin:0 auto;text-align:center;font-weight:900;height:36px;border-radius:8px;background:#f1f5f9;color:#0f172a;cursor:not-allowed">
                                @else
                                    <input type="number" step="0.5" min="0" max="{{ $exam->full_marks }}"
                                           name="marks[{{ $st->id }}]"
                                           class="form-control row-total comp-input"
                                           value="{{ $totalVal !== '' ? $totalVal : '' }}"
                                           placeholder="প্রাপ্ত নম্বর"
                                           style="width:110px;margin:0 auto;text-align:center;font-weight:900;height:36px;border-radius:8px">
                                @endif
                            </td>

                            {{-- Grade Preview --}}
                            <td style="padding:10px;text-align:center">
                                <span class="grade-pill" style="font-weight:800;font-size:12px;padding:3px 8px;border-radius:6px;display:inline-block;background:#e2e8f0;color:#334155">
                                    {{ $existingRes->grade ?? '—' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ 4 + ($exam->has_mcq ? 1 : 0) + ($exam->has_written ? 1 : 0) + ($exam->has_tamrin ? 1 : 0) + ($exam->has_viva ? 1 : 0) }}" style="text-align:center;padding:30px;color:var(--text-muted)">
                                কোনো সক্রিয় শিক্ষার্থী পাওয়া যায়নি।
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer" style="padding:16px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
                <div style="font-size:12px;color:#64748b">
                    <i class="fa-solid fa-lightbulb" style="color:#eab308"></i> নম্বর এন্ট্রি করার সাথে সাথে মোট স্কোর এবং গ্রেড স্বয়ংক্রিয়ভাবে পরিবর্তিত হবে।
                </div>
                <button type="submit" class="btn btn-success btn-lg" style="background:#16a34a;border-color:#16a34a;padding:10px 24px;font-weight:800;border-radius:10px;display:inline-flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-floppy-disk"></i> মূল্যায়ন নম্বর সংরক্ষণ করুন (Save Marks)
                </button>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fullMarks = {{ (float) ($exam->full_marks ?: 100) }};
            const passMarks = {{ (float) ($exam->pass_marks ?: 40) }};
            const hasComps = {{ $hasComps ? 'true' : 'false' }};

            function calculateGrade(marks, full) {
                if (full <= 0) return '—';
                const pct = (marks / full) * 100;
                if (pct >= 80) return 'A+';
                if (pct >= 70) return 'A';
                if (pct >= 60) return 'B';
                if (pct >= 50) return 'C';
                if (pct >= 40) return 'D';
                return 'F';
            }

            function updateRow(row) {
                let total = 0;
                let anyFilled = false;

                if (hasComps) {
                    const inputs = row.querySelectorAll('.comp-input');
                    inputs.forEach(inp => {
                        const val = parseFloat(inp.value);
                        if (!isNaN(val)) {
                            total += val;
                            anyFilled = true;
                        }
                    });
                    const totalInput = row.querySelector('.row-total');
                    if (totalInput) {
                        totalInput.value = anyFilled ? (Math.round(total * 100) / 100) : '';
                    }
                } else {
                    const totalInput = row.querySelector('.row-total');
                    if (totalInput) {
                        const val = parseFloat(totalInput.value);
                        if (!isNaN(val)) {
                            total = val;
                            anyFilled = true;
                        }
                    }
                }

                const gradePill = row.querySelector('.grade-pill');
                if (gradePill) {
                    if (anyFilled) {
                        const grade = calculateGrade(total, fullMarks);
                        gradePill.innerText = grade;
                        if (grade === 'F') {
                            gradePill.style.background = '#fee2e2';
                            gradePill.style.color = '#dc2626';
                        } else {
                            gradePill.style.background = '#dcfce7';
                            gradePill.style.color = '#15803d';
                        }
                    } else {
                        gradePill.innerText = '—';
                        gradePill.style.background = '#e2e8f0';
                        gradePill.style.color = '#334155';
                    }
                }
            }

            // Bind input event to all rows
            document.querySelectorAll('.eval-row').forEach(row => {
                row.querySelectorAll('input').forEach(inp => {
                    inp.addEventListener('input', () => updateRow(row));
                });
                // Initial calculation on load
                updateRow(row);
            });
        });
    </script>
</x-teacher-layout>
