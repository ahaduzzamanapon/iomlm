<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty ID Card — {{ $teacher->name }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap');
        body { font-family: 'Montserrat', sans-serif; background: #f1f5f9; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        
        .id-card-wrapper { display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; }
        .id-card { width: 270px; height: 430px; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1); position: relative; border: 1px solid #cbd5e1; display: flex; flex-direction: column; justify-content: space-between; }
        
        .card-header { background: linear-gradient(135deg, #065f46, #059669); color: #fff; text-align: center; padding: 16px 10px 10px; border-bottom: 4px solid #f59e0b; }
        .institute-name { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }
        .card-tag { font-size: 10px; background: #f59e0b; color: #0f172a; padding: 2px 8px; border-radius: 10px; font-weight: 700; display: inline-block; margin-top: 4px; }
        
        .card-body { text-align: center; padding: 12px 14px; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .photo-placeholder { width: 90px; height: 90px; border-radius: 50%; background: #ecfdf5; border: 3px solid #10b981; display: flex; align-items: center; justify-content: center; font-size: 36px; margin: 0 auto 10px; }
        .teacher-name { font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 2px; }
        .emp-code { font-size: 12px; font-weight: 700; color: #065f46; background: #d1fae5; padding: 2px 10px; border-radius: 12px; display: inline-block; }
        
        .info-list { font-size: 11px; text-align: left; width: 100%; margin-top: 10px; color: #334155; line-height: 1.6; }
        .info-list td { padding: 2px 0; }
        .info-label { font-weight: 700; color: #64748b; width: 85px; }
        
        .qr-section { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 8px 10px; text-align: center; display: flex; align-items: center; justify-content: space-around; }
        .qr-code { width: 50px; height: 50px; background: #065f46; color: #fff; font-size: 8px; display: flex; align-items: center; justify-content: center; border-radius: 6px; }

        @media print {
            body { background: #fff; padding: 0; }
            .id-card-wrapper { gap: 10px; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <div style="margin-bottom:20px" class="btn-print">
        <button onclick="window.print()" style="background:#059669;color:#fff;border:none;padding:12px 24px;border-radius:8px;font-weight:700;cursor:pointer">
            🖨️ Print Faculty Teacher ID Card
        </button>
    </div>

    <div class="id-card-wrapper">
        {{-- FRONT SIDE --}}
        <div class="id-card">
            <div class="card-header" style="display:flex;flex-direction:column;align-items:center;padding:12px 10px 8px">
                <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" style="height:36px;width:auto;object-fit:contain;margin-bottom:4px;filter:drop-shadow(0 2px 4px rgba(0,0,0,.2))">
                <h1 class="institute-name" style="font-size:12px">Islamic Online Madrasah</h1>
                <span class="card-tag">FACULTY TEACHER ID</span>
            </div>

            <div class="card-body">
                <div class="photo-placeholder">👨‍🏫</div>
                <h2 class="teacher-name">{{ $teacher->name }}</h2>
                <div class="emp-code">{{ $teacher->employee_id ?? 'EMP-2026-001' }}</div>

                <table class="info-list">
                    <tr><td class="info-label">Designation:</td><td><strong>{{ $teacher->designation ?? 'Lecturer' }}</strong></td></tr>
                    <tr><td class="info-label">Qualification:</td><td>{{ $teacher->qualification ?? 'B.Sc / M.Sc' }}</td></tr>
                    <tr><td class="info-label">Blood Grp:</td><td><strong style="color:#e11d48">{{ $teacher->blood_group ?? 'B+' }}</strong></td></tr>
                    <tr><td class="info-label">Email/Phone:</td><td>{{ $teacher->phone ?? $teacher->email }}</td></tr>
                </table>
            </div>

            <div class="qr-section">
                <div class="qr-code">QR CODE</div>
                <div style="font-size:9px;color:#64748b;text-align:left">
                    Official Faculty Verification<br>
                    <strong>Learning Plus System</strong>
                </div>
            </div>
        </div>

        {{-- BACK SIDE --}}
        <div class="id-card" style="background:#f8fafc">
            <div class="card-header" style="background:#0f172a">
                <h1 class="institute-name">Faculty Authorization</h1>
            </div>

            <div class="card-body" style="text-align:left;font-size:11px;color:#334155;line-height:1.5">
                <p><strong>Faculty Guidelines:</strong></p>
                <ul style="padding-left:16px;margin:0 0 12px">
                    <li>Authorized faculty member of Learning Plus Institute.</li>
                    <li>Always wear this badge on campus.</li>
                    <li>If found, please return to Admin Office.</li>
                </ul>

                <div style="margin-top:20px"><strong>Admin Hotline:</strong> +880 1700-000000</div>
            </div>

            <div class="qr-section" style="background:#065f46;color:#fff">
                <div style="font-size:10px;font-weight:700">Authorized Governing Body</div>
            </div>
        </div>
    </div>

</body>
</html>
