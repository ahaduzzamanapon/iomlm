<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student ID Card — {{ $student->name }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap');
        body { font-family: 'Montserrat', sans-serif; background: #f1f5f9; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        
        .id-card-wrapper { display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; }
        .id-card { width: 270px; height: 430px; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1); position: relative; border: 1px solid #cbd5e1; display: flex; flex-direction: column; justify-content: space-between; }
        
        .card-header { background: linear-gradient(135deg, #1e1b4b, #4338ca); color: #fff; text-align: center; padding: 16px 10px 10px; border-bottom: 4px solid #f59e0b; }
        .institute-name { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }
        .card-tag { font-size: 10px; background: #f59e0b; color: #0f172a; padding: 2px 8px; border-radius: 10px; font-weight: 700; display: inline-block; margin-top: 4px; }
        
        .card-body { text-align: center; padding: 12px 14px; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .photo-placeholder { width: 90px; height: 90px; border-radius: 50%; background: #e2e8f0; border: 3px solid #6366f1; display: flex; align-items: center; justify-content: center; font-size: 36px; margin: 0 auto 10px; }
        .student-name { font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 2px; }
        .student-code { font-size: 12px; font-weight: 700; color: #4338ca; background: #e0e7ff; padding: 2px 10px; border-radius: 12px; display: inline-block; }
        
        .info-list { font-size: 11px; text-align: left; width: 100%; margin-top: 10px; color: #334155; line-height: 1.6; }
        .info-list td { padding: 2px 0; }
        .info-label { font-weight: 700; color: #64748b; width: 80px; }
        
        .qr-section { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 8px 10px; text-align: center; display: flex; align-items: center; justify-content: space-around; }
        .qr-code { width: 50px; height: 50px; background: #0f172a; color: #fff; font-size: 8px; display: flex; align-items: center; justify-content: center; border-radius: 6px; }

        @media print {
            body { background: #fff; padding: 0; }
            .id-card-wrapper { gap: 10px; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <div style="margin-bottom:20px" class="btn-print">
        <button onclick="window.print()" style="background:#4338ca;color:#fff;border:none;padding:12px 24px;border-radius:8px;font-weight:700;cursor:pointer">
            🖨️ Print Digital Student ID Card
        </button>
    </div>

    <div class="id-card-wrapper">
        {{-- FRONT SIDE --}}
        <div class="id-card">
            <div class="card-header">
                <h1 class="institute-name">IOM Institute</h1>
                <span class="card-tag">STUDENT ID CARD</span>
            </div>

            <div class="card-body">
                <div class="photo-placeholder">👤</div>
                <h2 class="student-name">{{ $student->name }}</h2>
                <div class="student-code">{{ $student->student_code ?? 'STD-2026-000' }}</div>

                <table class="info-list">
                    <tr><td class="info-label">Batch:</td><td><strong>{{ $student->enrollments->first()?->batch?->name ?? '—' }}</strong></td></tr>
                    <tr><td class="info-label">Course:</td><td>{{ $student->enrollments->first()?->course?->name ?? '—' }}</td></tr>
                    <tr><td class="info-label">Blood Grp:</td><td><strong style="color:#e11d48">{{ $student->blood_group ?? 'O+' }}</strong></td></tr>
                    <tr><td class="info-label">Phone:</td><td>{{ $student->phone }}</td></tr>
                </table>
            </div>

            <div class="qr-section">
                <div class="qr-code">QR CODE</div>
                <div style="font-size:9px;color:#64748b;text-align:left">
                    Authentic Student Verification<br>
                    <strong>IOM System</strong>
                </div>
            </div>
        </div>

        {{-- BACK SIDE --}}
        <div class="id-card" style="background:#f8fafc">
            <div class="card-header" style="background:#0f172a">
                <h1 class="institute-name">Emergency &amp; Rules</h1>
            </div>

            <div class="card-body" style="text-align:left;font-size:11px;color:#334155;line-height:1.5">
                <p><strong>Instructions:</strong></p>
                <ul style="padding-left:16px;margin:0 0 12px">
                    <li>This card is non-transferable.</li>
                    <li>Always wear this card inside campus.</li>
                    <li>If found, please return to the institute administration.</li>
                </ul>

                <p><strong>Emergency Contact:</strong></p>
                <div>Father: {{ $student->father_name ?? '—' }}</div>
                <div>Guardian Phone: {{ $student->guardian_phone ?? $student->phone }}</div>
                <div style="margin-top:10px"><strong>Institute Hotline:</strong> +880 1700-000000</div>
            </div>

            <div class="qr-section" style="background:#0f172a;color:#fff">
                <div style="font-size:10px;font-weight:700">Authorized Registrar Signature</div>
            </div>
        </div>
    </div>

</body>
</html>
