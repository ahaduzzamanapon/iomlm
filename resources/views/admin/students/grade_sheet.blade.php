<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Academic Grade Sheet — {{ $student->name }}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8fafc; margin: 0; padding: 20px; color: #0f172a; }
        .sheet-card { max-width: 800px; margin: 0 auto; background: #fff; border: 2px solid #0f172a; border-radius: 12px; padding: 40px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 2px dashed #cbd5e1; padding-bottom: 20px; margin-bottom: 20px; }
        .institute-title { font-size: 26px; font-weight: 800; color: #0f172a; text-transform: uppercase; margin: 0; }
        .sheet-sub { font-size: 14px; font-weight: 700; color: #4f46e5; margin-top: 6px; letter-spacing: 1px; }

        .student-info { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; font-size: 13px; background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .info-item { font-size: 13px; }
        .info-label { font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; }

        .table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 13px; }
        .table th, .table td { border: 1px solid #cbd5e1; padding: 10px 12px; text-align: left; }
        .table th { background: #f1f5f9; color: #334155; font-weight: 700; text-transform: uppercase; font-size: 11px; }

        .footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 60px; font-size: 12px; color: #64748b; }
        .sign-line { border-top: 1px solid #0f172a; width: 180px; text-align: center; padding-top: 6px; font-weight: 700; color: #0f172a; }

        @media print {
            body { background: #fff; padding: 0; }
            .sheet-card { border: none; box-shadow: none; padding: 0; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <div style="text-align:center;margin-bottom:20px" class="btn-print">
        <button onclick="window.print()" style="background:#6366f1;color:#fff;border:none;padding:12px 24px;border-radius:8px;font-weight:700;cursor:pointer">
            Print Official Academic Transcript &amp; Grade Sheet
        </button>
    </div>

    <div class="sheet-card">
        <div class="header" style="display:flex;flex-direction:column;align-items:center">
            <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" style="height:52px;width:auto;object-fit:contain;margin-bottom:6px">
            <h1 class="institute-title">Islamic Online Madrasah</h1>
            <div class="sheet-sub">OFFICIAL ACADEMIC TRANSCRIPT &amp; GRADE SHEET</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px">Issued Date: {{ date('d F Y') }}</div>
        </div>

        <div class="student-info">
            <div>
                <div class="info-label">Student Name</div>
                <strong>{{ $student->name }}</strong><br>
                <div class="info-label" style="margin-top:8px">Student ID Code</div>
                <strong style="color:#4f46e5">{{ $student->student_code ?? 'N/A' }}</strong>
            </div>
            <div>
                <div class="info-label">Current Enrolled Batch</div>
                <strong>{{ $student->enrollments->first()?->batch?->name ?? '—' }}</strong><br>
                <div class="info-label" style="margin-top:8px">Course Program</div>
                <strong>{{ $student->enrollments->first()?->course?->name ?? $student->enrollments->first()?->batch?->course?->name ?? '—' }}</strong>
            </div>
        </div>

        <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:10px">Examination Results &amp; Marks Breakdown:</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Subject Code &amp; Title</th>
                    <th>Exam Title</th>
                    <th>Full Marks</th>
                    <th>Marks Obtained</th>
                    <th>Grade / Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($student->results as $res)
                <tr>
                    <td>
                        <strong>{{ $res->exam?->subject?->name ?? '—' }}</strong><br>
                        <small style="color:#64748b">{{ $res->exam?->subject?->code }}</small>
                    </td>
                    <td>{{ $res->exam?->title ?? '—' }}</td>
                    <td>{{ $res->exam?->full_marks ?? 100 }}</td>
                    <td><strong style="color:#10b981;font-size:14px">{{ number_format($res->marks, 2) }}</strong></td>
                    <td>
                        @php
                            $stClass = match($res->status) { 'PASS'=>'color:#10b981;font-weight:700', default=>'color:#e11d48;font-weight:700' };
                        @endphp
                        <span style="{{ $stClass }}">{{ $res->status ?? 'COMPLETED' }} ({{ $res->grade ?? 'A' }})</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:30px;color:#94a3b8">
                        No examination records found for this student.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <div>
                Verified By: <strong>Controller of Examinations</strong><br>
                <small>Authentic Record generated from IOM LMS</small>
            </div>
            <div class="sign-line">
                Principal / Controller Signature
            </div>
        </div>
    </div>

</body>
</html>
